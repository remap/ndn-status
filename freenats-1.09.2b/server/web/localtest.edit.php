<?php
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008 PurplePixie Systems

FreeNATS is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

FreeNATS is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with FreeNATS.  If not, see www.gnu.org/licenses

For more information see www.purplepixie.org/freenats
-------------------------------------------------------------- */

ob_start();
require("include.php");
$NATS->Start();
if (!$NATS_Session->Check($NATS->DB))
	{
	header("Location: ./?login_msg=Invalid+Or+Expired+Session");
	exit();
	}
if ($NATS_Session->userlevel<5) UL_Error("Edit Local Test");
ob_end_flush();
Screen_Header("Local Test Editor",1,1,"","main","nodes");
?>
<br>
<?php
$q="SELECT * FROM fnlocaltest WHERE localtestid=".ss($_REQUEST['localtestid'])." LIMIT 0,1";
$r=$NATS->DB->Query($q);
if (!$row=$NATS->DB->Fetch_Array($r))
	{
	echo "No such test!<br><br>";
	Screen_Footer();
	exit();
	}
if ($row['testname']!="") $nicename=$row['testname'];
else $nicename=lText($row['testtype']);

echo "<b class=\"subtitle\">Editing Test: <a href=node.edit.php?nodeid=".$row['nodeid'].">".$row['nodeid']."</a> &gt; ".$nicename."</b><br><br>";

if (isset($_REQUEST['message'])) 
	{
	echo "<b>".$_REQUEST['message']."</b><br><br>";
	$poplist[]=$_REQUEST['message'];
	}
$title="<b class=\"sectitle\">Test Settings</b>";
Start_Round($title,600);
	
echo "<table border=0 width=100%>";
echo "<form action=localtest.action.php method=post>";
echo "<input type=hidden name=localtestid value=".$_REQUEST['localtestid'].">";
echo "<input type=hidden name=action value=save_form>";
echo "<tr><td><b>Test Type: </b></td>";
echo "<td><b>".lText($row['testtype']);
echo "</b></td></tr>";
echo "<tr><td colspan=2><hr class=\"nspacer\"></td></tr>";
echo "<tr><td valign=top align=left>Test Options:</td><td>"; // new style multi-param options

switch ($row['testtype'])
	{
	case "web": case "wsize": case "wtime":	
		echo "URL: <input type=text name=testparam size=30 maxlength=128 value=\"".$row['testparam']."\"><br>";
		echo "<i>Full URL such as https://www.somehost.com/</i>";
		break;

	case "icmp": case "ping":	
		echo "Host/IP: <input type=text name=testparam size=30 maxlength=128 value=\"".$row['testparam']."\"><br>";
		echo "<i>DNS hostname or IP address to ping (DNS lookup not timed)</i>";
		break;
		
	case "testloop": case "testrand":	
		echo "Value: <input type=text name=testparam size=30 maxlength=128 value=\"".$row['testparam']."\"><br>";
		echo "<i>Value for loop or rand tests (see documentation)</i>";
		break;
	
	case "host":	
		echo "Host: <input type=text name=testparam size=30 maxlength=128 value=\"".$row['testparam']."\"><br>";
		echo "<i>Hostname to resolve or IP to reverse lookup</i>";
		break;

	default:
	
		$idx=$NATS->Tests->Get($row['testtype']);
		if (is_object($idx))
			{
			// Test-specific formatting here
			
			$pcount=0; // param count
			//echo $NATS->Tests->Tests[$row['testtype']]->parameters;
			if (isset($NATS->Tests->Tests[$row['testtype']]->parameters) && is_array($NATS->Tests->Tests[$row['testtype']]->parameters)
				&& (count($NATS->Tests->Tests[$row['testtype']]->parameters)>0) )
				{
				echo "<table border=0>";
				foreach($NATS->Tests->Tests[$row['testtype']]->parameters as $param)
					{
					$pname="testparam";
					if ($pcount>0) $pname.=$pcount;
					$undertext="";
					$pos=strpos($param,"/");
					if ($pos>0)
						{
						$undertext=substr($param,$pos+1);
						$param=substr($param,0,$pos);
						}
					echo "<tr><td align=right>";
					echo $param;
					echo " :</td><td align=left>";
					echo "<input type=text name=\"".$pname."\" value=\"".$row[$pname]."\" size=30 maxlength=128>";
					echo "</td></tr>";
					if ($undertext!="")
						{
						echo "<tr><td>&nbsp;</td><td align=left><i>".$undertext."</i></td></tr>";
						}
					$pcount++;
					}
				echo "</table>";
				}
			else
				{
				$NATS->Tests->Tests[$row['testtype']]->Create();
				$out=$NATS->Tests->Tests[$row['testtype']]->instance->DisplayForm($row);
				if ($out===false) echo "<i>No options for test</i>";
				else echo $out;
				}
			
			
			
			
			}
		
	
		// And the catch-all of catch-alls
		else echo "<input type=text name=testparam size=30 maxlength=128 value=\"".$row['testparam']."\">";
	}

echo "</td></tr>";
echo "<tr><td colspan=2><hr class=\"nspacer\"></td></tr>";
echo "<tr><td>Custom Name:</td>";
echo "<td><input type=text size=30 name=testname maxlength=64 value=\"".$row['testname']."\"> ".hlink("Test:Name");
echo "</td></tr>";
echo "<tr><td>Test Enabled:</td>";
echo "<td>";
if ($row['testenabled']==1) $s=" checked";
else $s="";
echo "<input type=checkbox name=testenabled value=1".$s."> ";
echo hlink("Test:Enabled");
echo "</td></tr>";
echo "<tr><td>Recorded:</td><td>";
if ($row['testrecord']==1) $s=" checked";
else $s="";
echo "<input type=checkbox name=testrecord value=1".$s."> ".hlink("Test:Recorded");
echo "</td></tr>";
echo "<tr><td>Simple Evaluation:</td><td>";
if ($row['simpleeval']==1) $s=" checked";
else $s="";
echo "<input type=checkbox name=simpleeval value=1".$s."> ".hlink("Test:SimpleEvaluation");
echo "</td></tr>";
echo "<tr><td colspan=2><hr class=\"nspacer\"></td></tr>";
echo "<tr><td>Test Interval:</td>";
echo "<td><input type=text size=3 name=testinterval maxlength=8 value=\"".$row['testinterval']."\"> Minutes ".hlink("Test:Interval");
echo "</td></tr>";
echo "<input type=hidden name=original_testinterval value=\"".$row['testinterval']."\">";
echo "<tr><td valign=top>Test Due:</td>";
echo "<td>";
if ($row['nextrunx']>0) echo nicedt($row['nextrunx'])." - ".nicenextx($row['nextrunx']);
else echo "Now";
echo "</td></tr>";
echo "<tr><td valign=top>Last Tested:</td>";
echo "<td>".nicedt($row['lastrunx'])." - ".dtago($row['lastrunx'])."<br>";
echo "<a href=localtest.action.php?localtestid=".$_REQUEST['localtestid']."&action=invalidate>Check ASAP</a>";
echo "</td></tr>";
echo "<tr><td colspan=2><hr class=\"nspacer\"></td></tr>";
echo "<tr><td>Custom Attempts:</td>";
echo "<td><input type=text size=3 name=attempts maxlength=2 value=\"".$row['attempts']."\"> ".hlink("Test:Attempts");
echo "</td></tr>";
echo "<tr><td>Custom Timeout:</td>";
echo "<td><input type=text name=timeout size=3 maxlength=3 value=\"".$row['timeout']."\"> Seconds ".hlink("Test:Timeout");
echo "</td></tr>";
echo "<tr><td colspan=2><hr class=\"nspacer\"></td></tr>";
echo "<tr><td>&nbsp;</td><td><input type=submit value=\"Save Test Settings\"> <a href=node.edit.php?nodeid=".$row['nodeid'].">Abandon Changes</a>";
echo "</td></tr></form>";
echo "</table>";
End_Round();
echo "<br><br>";



$t="<b class=\"sectitle\">Test Evaluators</b>";
Start_Round($t,600);


echo "<table border=0 width=100%>";
echo "<tr><td colspan=2>&nbsp;<br>";
if ($row['simpleeval']==1)
	{
	echo "<i>Custom evaluators will not be processed as<br>Simple Evaluation is checked (above)</i><br>";
	}
echo "</td></tr>";

$q="SELECT * FROM fneval WHERE testid=\"L".ss($_REQUEST['localtestid'])."\" ORDER BY weight ASC";
$r=$NATS->DB->Query($q);
while ($row=$NATS->DB->Fetch_Array($r))
	{
	echo "<tr><td colspan=2>";
	echo "<a href=eval.action.php?action=delete&back=".urlencode("localtest.edit.php?localtestid=".$_REQUEST['localtestid']."&message=Evaluator+Deleted")."&evalid=".$row['evalid'].">";
	echo "<img src=images/options/action_delete.png border=0 style=\"vertical-align: bottom;\"></a>&nbsp;&nbsp;";	
	echo "Result ".eval_operator_text($row['eoperator'])." ".$row['evalue']." =&gt; ".oText($row['eoutcome'])."";
	//echo " | <a href=eval.action.php?action=move&dir=up&evalid=".$row['evalid'].">Up</a>/<a href=eval.action.php?action=move&dir=dn&evalid=".$row['evalid'].">Down</a>";
	echo "</td></tr>";
	//echo "<tr><td colspan=2>&nbsp;</td></tr>";
	}

echo "<form action=eval.action.php>";
echo "<input type=hidden name=action value=create>";
echo "<input type=hidden name=testid value=L".$_REQUEST['localtestid'].">";
echo "<tr><td colspan=2>&nbsp;<br></td></tr>";
echo "<tr><td><b>Add New :</b></td>";
echo "<td><select name=eoperator>";
echo "<option value=ET>Equal To</option><option value=LT>Less Than</option><option value=GT>Greater Than</option>";
echo "</select> <input type=text name=evalue size=4 value=0> =&gt; ";
echo "<select name=eoutcome>";
echo "<option value=1>Warning</option>";
echo "<option value=2>Failure</option>";
echo "</select> <input type=submit value=Add></td></tr>";
echo "</form>";


echo "</table>";
End_Round();

?>

<?php
Screen_Footer();
?>
