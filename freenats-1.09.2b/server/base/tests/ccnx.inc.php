<?php // ccnx.inc.php -- CCN test module

if (isset($NATS)) {
	class FreeNATS_CCN_Test extends FreeNATS_Local_Test {
		function DoTest($testname, $param, $hostname, $timeout, $params) { 
			global $NATS;

			$ccnping = "/var/www/ccnping";
			$cmd = sprintf("%s %s", $ccnping, escapeshellcmd($param));
			$output = @shell_exec($cmd);
			$res = preg_split("/\s/", $output, -1, PREG_SPLIT_NO_EMPTY);

			if (preg_match("/^\d+(\.\d+)?$/", $res[0])) {
				$res[0] /= 1000;
				return $res[0];
			} elseif (preg_match("/^timeout/", $res[0])) {
				return -1;
			}

			return -2;
		}

		function Evaluate($result) {
			if ($result < 0)
				return 2; // failure

			return 0; // else success
		}

		function DisplayForm(&$row) {
			echo "<table border=0>";
			echo "<tr><td align=left>";
			echo "CCN URI :";
			echo "</td><td align=left>";
			echo "<input type=text name=testparam size=30 maxlength=128 value=\"".$row['testparam']."\">";
			echo "</td></tr>";
			echo "</table>";
		}

	}

	$params=array();
	$NATS->Tests->Register("ccn", "FreeNATS_CCN_Test", $params, "CCN Ping", 1, "FreeNATS CCN Tester");
	$NATS->Tests->SetUnits("ccn", "Seconds", "s");
}

?>
