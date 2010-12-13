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
import sun.misc.Signal;
import sun.misc.SignalHandler;

/**
 *
 * @author takeda
 */
public class Main {

	/**
	 * @param args the command line arguments
	 */
	public static void main(String[] args) throws MalformedContentNameStringException {
		ContentName namespace = ContentName.fromURI("ccnx:/blah");
		final PathChar pathchar = new PathChar(namespace);
		final Status status = new Status(namespace);

		Signal.handle(new Signal("INT"), new SignalHandler() {

			public void handle(Signal signal) {
				System.err.print("Shuttting down ...");
				pathchar.stopListening();
				status.stopListening();
				System.err.println("DONE!");

				System.exit(0);
			}
		});

		try {
			pathchar.startListening();
			status.startListening();
		} catch (IOException ex) {
			Logger.getLogger(Main.class.getName()).log(Level.SEVERE, null, ex);
		}
	}
}
