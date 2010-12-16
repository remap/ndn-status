/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package ndnstatus;

import java.io.IOException;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.ccnx.ccn.protocol.ContentName;
import org.ccnx.ccn.protocol.MalformedContentNameStringException;

/**
 *
 * @author takeda
 */
public class Main {

	private static void usage() {
		System.err.println("ndn-status <URI>");
		System.exit(10);
	}

	/**
	 * @param args the command line arguments
	 */
	public static void main(String[] args) {
		ContentName namespace;
		PathChar pathchar;
		Status status;

		if (args.length != 1)
			usage();

		try {
			namespace = ContentName.fromURI(args[0]);

			pathchar = new PathChar(namespace);
			status = new Status(namespace);

			pathchar.startListening();
			status.startListening();
		} catch (IOException ex) {
			Logger.getLogger(Main.class.getName()).log(Level.SEVERE, null, ex);
		} catch (MalformedContentNameStringException ex) {
			usage();
		}
	}
}
