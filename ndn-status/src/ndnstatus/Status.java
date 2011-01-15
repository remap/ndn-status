/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package ndnstatus;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.net.URL;
import java.security.PrivateKey;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;
import org.ccnx.ccn.CCNFilterListener;
import org.ccnx.ccn.CCNHandle;
import org.ccnx.ccn.KeyManager;
import org.ccnx.ccn.io.CCNOutputStream;
import org.ccnx.ccn.io.CCNVersionedOutputStream;
import org.ccnx.ccn.profiles.VersioningProfile;
import org.ccnx.ccn.protocol.ContentName;
import org.ccnx.ccn.protocol.ContentObject;
import org.ccnx.ccn.protocol.Interest;
import org.ccnx.ccn.protocol.KeyLocator;
import org.ccnx.ccn.protocol.PublisherPublicKeyDigest;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXException;

/**
 * Not sure if there's an interface to directly get those information
 * @author Derek Kulinski <takeda@takeda.tk>
 */
public final class Status implements CCNFilterListener {
	private final String STATUS_URL = "http://127.0.0.1:9695/";
	private final String STATUS_XML = "?f=xml";
	private final CCNHandle _ccn_handle;
	private final ContentName _service_uri;
	private final PrivateKey _signing_key;
	private final PublisherPublicKeyDigest _publisher;
	private final KeyLocator _locator;

	public Status(ContentName namespace)
	{
		_ccn_handle = CCNHandle.getHandle();
		_service_uri = ContentName.fromNative(namespace, "status");

		KeyManager keymanager = _ccn_handle.keyManager();
		this._signing_key = keymanager.getDefaultSigningKey();
		this._publisher = keymanager.getPublisherKeyID(_signing_key);
		this._locator = keymanager.getKeyLocator(_signing_key);
	}

	public void startListening()
					throws IOException
	{
		_ccn_handle.registerFilter(_service_uri, this);
	}

	public void stopListening()
	{
		_ccn_handle.unregisterFilter(_service_uri, this);
	}

	private StringBuilder parseValues(Document doc, String tag)
	{
		StringBuilder sb = new StringBuilder();
		Node tmpNode, tmpNode2;
		NodeList tmpNodeList;

		tmpNodeList = doc.getElementsByTagName(tag);
		if (tmpNodeList.getLength() != 1)
			return sb.append("* I expected only one ").append(tag).append(" tag *");

		Node tagNode = tmpNodeList.item(0);
		if (tagNode.getNodeType() != Node.ELEMENT_NODE)
			return sb.append("* ").append(tag).append(" node is not an element node *");

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

	private StringBuilder parseFace(Node face)
	{
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

	private StringBuilder parseFaces(Document doc)
	{
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

	private StringBuilder parseFentry(Node fe)
	{
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

	private StringBuilder parseForwarding(Document doc)
	{
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

	private StringBuilder generateTextStatus(Interest interest)
	{
		StringBuilder sb = new StringBuilder();
		DocumentBuilderFactory dbf = DocumentBuilderFactory.newInstance();
		DocumentBuilder db;
		Document doc;

		try {
			db = dbf.newDocumentBuilder();
			doc = db.parse(STATUS_URL + STATUS_XML);
			doc.getDocumentElement().normalize();

			sb.append("Content items:");
			sb.append(parseValues(doc, "cobs"));
			sb.append("Interests:");
			sb.append(parseValues(doc, "interests"));
			sb.append("Faces:\n");
			sb.append(parseFaces(doc));
			sb.append("Forwarding:\n");
			sb.append(parseForwarding(doc));
		}
		catch (SAXException ex) {
			sb.append('\n').append(ex.getMessage());
		}
		catch (IOException ex) {
			sb.append('\n').append(ex.getMessage());
		}
		catch (ParserConfigurationException ex) {
			sb.append('\n').append(ex.getMessage());
		}

		return sb;
	}

	private StringBuilder generateMLStatus(URL url, Interest interest)
	{
		StringBuilder sb = new StringBuilder();
		String str;

		try {
			BufferedReader in = new BufferedReader(new InputStreamReader(
							url.openStream()));

			while ((str = in.readLine()) != null) {
				sb.append(str);
				sb.append('\n');
			}

			in.close();
		}
		catch (IOException ex) {
			sb.append('\n').append(ex.getMessage());
		}

		return sb;
	}

	public boolean handleInterest(Interest interest)
	{
		StringBuilder sb;
		ContentObject co;
		ContentName name, postfix;

		if ((interest.answerOriginKind() & Interest.ANSWER_GENERATED) == 0)
			return true;

		//Ignore specific version requests (is this correct?)
		if (VersioningProfile.hasTerminalVersion(interest.name()))
			return false;

		try {
			postfix = interest.name().postfix(_service_uri);

			if (postfix.count() == 0)
				sb = generateTextStatus(interest);
			else if (postfix.toString().equals("/html")) {
				URL url = new URL(STATUS_URL);
				sb = generateMLStatus(url, interest);
			} else if (postfix.toString().equals("/xml")) {
				URL url = new URL(STATUS_URL + STATUS_XML);
				sb = generateMLStatus(url, interest);
			} else {
				System.err.println("Invalid postfix: " + postfix.toString());
				return true;
			}

			byte[] data = sb.toString().getBytes();

			CCNOutputStream os = new CCNVersionedOutputStream(interest.name(),
							_ccn_handle);

			os.addOutstandingInterest(interest);
			os.setFreshnessSeconds(60);
			os.write(data, 0, data.length);
			os.close();

			return true;
		}
		catch (IOException ex) {
			Logger.getLogger(Status.class.getName()).log(Level.SEVERE, null, ex);
		}

		return false;
	}
}
