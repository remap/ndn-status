/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package ndnstatus;

import java.io.IOException;
import java.security.PrivateKey;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.ccnx.ccn.CCNFilterListener;
import org.ccnx.ccn.CCNHandle;
import org.ccnx.ccn.KeyManager;
import org.ccnx.ccn.io.CCNOutputStream;
import org.ccnx.ccn.io.CCNVersionedOutputStream;
import org.ccnx.ccn.profiles.VersioningProfile;
import org.ccnx.ccn.protocol.ContentName;
import org.ccnx.ccn.protocol.Interest;
import org.ccnx.ccn.protocol.KeyLocator;
import org.ccnx.ccn.protocol.MalformedContentNameStringException;
import org.ccnx.ccn.protocol.PublisherPublicKeyDigest;

/**
 *
 * @author Derek Kulinski <takeda@takeda.tk>
 */
final public class PathChar implements CCNFilterListener {
	final private CCNHandle _ccn_handle;
	final private ContentName _service_uri;
	final private PrivateKey _signing_key;
	final private PublisherPublicKeyDigest _publisher;
	final private KeyLocator _locator;

	public PathChar(ContentName namespace)
					throws MalformedContentNameStringException
	{
		_ccn_handle = CCNHandle.getHandle();
		_service_uri = ContentName.fromNative(namespace, "pathchar");

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

	public boolean handleInterest(Interest interest)
	{
		ContentName name = interest.name();

		if ((interest.answerOriginKind() & Interest.ANSWER_GENERATED) == 0)
			return true;

		//Ignore specific version requests (is this correct?)
		if (VersioningProfile.hasTerminalVersion(interest.name()))
			return false;

		try {
			CCNOutputStream os = new CCNVersionedOutputStream(name, _ccn_handle);
			os.addOutstandingInterest(interest);
			os.setFreshnessSeconds(5);
			os.close();

			return true;
		}
		catch (IOException ex) {
			Logger.getLogger(PathChar.class.getName()).log(Level.SEVERE, null, ex);
		}

		return false;
	}
}
