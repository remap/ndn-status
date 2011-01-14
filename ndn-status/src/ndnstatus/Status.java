/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package ndnstatus;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.net.MalformedURLException;
import java.net.URL;
import java.security.InvalidKeyException;
import java.security.PrivateKey;
import java.security.SignatureException;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;
import org.ccnx.ccn.CCNFilterListener;
import org.ccnx.ccn.CCNHandle;
import org.ccnx.ccn.KeyManager;
import org.ccnx.ccn.protocol.ContentName;
import org.ccnx.ccn.protocol.ContentObject;
import org.ccnx.ccn.protocol.Interest;
import org.ccnx.ccn.protocol.KeyLocator;
import org.ccnx.ccn.protocol.PublisherPublicKeyDigest;
import org.ccnx.ccn.protocol.SignedInfo;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXException;

/**
 * Not sure if there's an interface to directly get those information
 * @author takeda
 */
public final class Status implements CCNFilterListener {

	private final String STATUS_URL = "http://127.0.0.1:9695/";
	private final String STATUS_XML = "?f=xml";
	private final CCNHandle _ccn_handle;
	private final ContentName _service_uri;
	private final PrivateKey _signing_key;
	private final PublisherPublicKeyDigest _publisher;
	private final KeyLocator _locator;

	public Status(ContentName namespace) {
		_ccn_handle = CCNHandle.getHandle();
		_service_uri = ContentName.fromNative(namespace, "status");

		KeyManager keymanager = _ccn_handle.keyManager();
		this._signing_key = keymanager.getDefaultSigningKey();
		this._publisher = keymanager.getPublisherKeyID(_signing_key);
		this._locator = keymanager.getKeyLocator(_signing_key);
	}

	public void startListening() throws IOException {
		_ccn_handle.registerFilter(_service_uri, this);
	}

	public void stopListening() {
		_ccn_handle.unregisterFilter(_service_uri, this);
	}

	private StringBuilder parseValues(Document doc, String tag) {
		StringBuilder sb = new StringBuilder();
		Node tmpNode, tmpNode2;
		NodeList tmpNodeList;

		tmpNodeList = doc.getElementsByTagName(tag);
		if (tmpNodeList.getLength() != 1)
			return sb.append("* I expected only one " + tag + " tag *");

		Node tagNode = tmpNodeList.item(0);
		if (tagNode.getNodeType() != Node.ELEMENT_NODE)
			return sb.append("* " + tag + " node is not an element node *");

		tmpNodeList = tagNode.getChildNodes();
		for (int i = 0; i < tmpNodeList.getLength(); i++) {
			tmpNode = tmpNodeList.item(i);
			tmpNode2 = tmpNode.getFirstChild();

			sb.append(" ").append(tmpNode.getNodeName());
			sb.append(": ").append(tmpNode2.getNodeValue());
		}
		sb.append('\n');

		return sb;
	}

	private StringBuilder parseFace(Node face) {
		StringBuilder sb = new StringBuilder();
		Node tmpNode, tmpNode2;
		NodeList tmpNodeList;

		tmpNodeList = face.getChildNodes();
		for (int i = 0; i < tmpNodeList.getLength(); i++) {
			tmpNode = tmpNodeList.item(i);

			if (tmpNode.getNodeType() != Node.ELEMENT_NODE)
				continue;

			tmpNode2 = tmpNode.getFirstChild();
			if (tmpNode2.getNodeType() != Node.TEXT_NODE)
				continue;

			sb.append(" ").append(tmpNode.getNodeName());
			sb.append(": ").append(tmpNode2.getNodeValue());
		}

		return sb;
	}

	private StringBuilder parseFaces(Document doc) {
		StringBuilder sb = new StringBuilder();
		Node tmpNode;
		NodeList tmpNodeList;
		Element faces;

		tmpNodeList = doc.getElementsByTagName("faces");
		if (tmpNodeList.getLength() != 1)
			return sb.append("* expected only one faces tag *");

		tmpNode = tmpNodeList.item(0);
		if (tmpNode.getNodeType() != Node.ELEMENT_NODE)
			return sb.append("* expected faces to be of type Element *");

		faces = (Element) tmpNode;

		tmpNodeList = faces.getElementsByTagName("face");
		for (int i = 0; i < tmpNodeList.getLength(); i++) {
			tmpNode = tmpNodeList.item(i);

			sb.append(parseFace(tmpNode));
			sb.append('\n');
		}

		return sb;
	}

	private StringBuilder parseFentry(Node fe) {
		StringBuilder sb = new StringBuilder();
		Element fentry = (Element) fe;

		Node prefix = fentry.getElementsByTagName("prefix").item(0);
		sb.append(' ');
		sb.append(prefix.getChildNodes().item(0).getNodeValue());

		Node dest = fentry.getElementsByTagName("dest").item(0);
		NodeList destList = dest.getChildNodes();
		for (int i = 0; i < destList.getLength(); i++) {
			Node name = destList.item(i);
			Node value = name.getFirstChild();

			sb.append(' ').append(name.getNodeName());
			sb.append(": ").append(value.getNodeValue());
		}

		return sb;
	}

	private StringBuilder parseForwarding(Document doc) {
		StringBuilder sb = new StringBuilder();
		Node tmpNode;
		NodeList tmpNodeList, fentryList;
		Element forwarding;

		tmpNodeList = doc.getElementsByTagName("forwarding");
		if (tmpNodeList.getLength() != 1)
			return sb.append("* expected only one forwarding tag *");

		tmpNode = tmpNodeList.item(0);
		if (tmpNode.getNodeType() != Node.ELEMENT_NODE)
			return sb.append("* expected forwarding to be of type Element *");

		forwarding = (Element) tmpNode;
		fentryList = forwarding.getElementsByTagName("fentry");
		for (int i = 0; i < fentryList.getLength(); i++) {
			Node tmp = fentryList.item(i);

			if (tmp.getNodeType() != Node.ELEMENT_NODE)
				continue;

			sb.append(parseFentry(tmp));
			sb.append('\n');
		}

		return sb;
	}

	private boolean handleTextStatus(Interest interest) {
		try {
			StringBuilder sb = new StringBuilder();
			DocumentBuilderFactory dbf = DocumentBuilderFactory.newInstance();
			DocumentBuilder db = dbf.newDocumentBuilder();
			Document doc = db.parse(STATUS_URL + STATUS_XML);
			doc.getDocumentElement().normalize();

			sb.append("Content items:");
			sb.append(parseValues(doc, "cobs"));
			sb.append("Interests:");
			sb.append(parseValues(doc, "interests"));
			sb.append("Faces:\n");
			sb.append(parseFaces(doc));
			sb.append("Forwarding:\n");
			sb.append(parseForwarding(doc));

			SignedInfo si = new SignedInfo(_publisher, SignedInfo.ContentType.DATA,
							_locator, 60, null);

			ContentObject co = new ContentObject(interest.name(), si, sb.toString().getBytes(), _signing_key);
			_ccn_handle.put(co);

			return true;
		} catch (InvalidKeyException ex) {
			Logger.getLogger(Status.class.getName()).log(Level.SEVERE, null, ex);
		} catch (SignatureException ex) {
			Logger.getLogger(Status.class.getName()).log(Level.SEVERE, null, ex);
		} catch (SAXException ex) {
			Logger.getLogger(Status.class.getName()).log(Level.SEVERE, null, ex);
		} catch (IOException ex) {
			Logger.getLogger(Status.class.getName()).log(Level.SEVERE, null, ex);
		} catch (ParserConfigurationException ex) {
			Logger.getLogger(Status.class.getName()).log(Level.SEVERE, null, ex);
		}

		return false;
	}

	private boolean handleMLStatus(URL url, Interest interest) {
		try {
			BufferedReader in = new BufferedReader(new InputStreamReader(url.openStream()));
			String str;
			StringBuilder sb = new StringBuilder();

			while ((str = in.readLine()) != null) {
				sb.append(str);
				sb.append('\n');
			}
			in.close();

			byte[] content = sb.toString().getBytes();

			SignedInfo si = new SignedInfo(_publisher, SignedInfo.ContentType.DATA,
							_locator, 60, null);
			ContentObject co = new ContentObject(interest.name(), si, content, _signing_key);
			_ccn_handle.put(co);

			return true;
		} catch (InvalidKeyException ex) {
			Logger.getLogger(Status.class.getName()).log(Level.SEVERE, null, ex);
		} catch (SignatureException ex) {
			Logger.getLogger(Status.class.getName()).log(Level.SEVERE, null, ex);
		} catch (IOException ex) {
			Logger.getLogger(Status.class.getName()).log(Level.SEVERE, null, ex);
		}

		return false;
	}

	public boolean handleInterest(Interest interest) {
		ContentName postfix = interest.name().postfix(_service_uri);

		if ((interest.answerOriginKind() & Interest.ANSWER_GENERATED) == 0)
			return true;

		try {
			if (postfix.count() == 0)
				return handleTextStatus(interest);
			else if (postfix.toString().equals("/html")) {
				URL url = new URL(STATUS_URL);
				return handleMLStatus(url, interest);

			} else if (postfix.toString().equals("/xml")) {
				URL url = new URL(STATUS_URL + STATUS_XML);
				return handleMLStatus(url, interest);
			}

		} catch (MalformedURLException ex) {
			Logger.getLogger(Status.class.getName()).log(Level.SEVERE, null, ex);
		}

		return false;
	}
}
