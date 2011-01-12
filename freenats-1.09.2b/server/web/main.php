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
ob_end_flush();

if (isset($_REQUEST['mode'])) $mode=$_REQUEST['mode'];
else 
	{
	$mode="overview";
	$_REQUEST['mode']="overview";
	}

Screen_Header("Monitoring Configuration",1,0,"","main");

if (isset($_REQUEST['message'])) 
	{
	echo "<b>".$_REQUEST['message']."</b><br>";
	$poplist[]=$_REQUEST['message'];
	}
	
if (isset($_REQUEST['nodemove'])) $nm=true;
else $nm=false;

function dispyn($val)
{
if ($val==0) return "N";
else if ($val==1) return "Y";
else return $val."?";
}

?>
<br>
<?php
if (isset($_REQUEST['check_updates']))
	{
	// check for updates
	$dq="?CheckVersion=".$NATS->Version."&JSMode=1";
	$dl="http://www.purplepixie.org/freenats/download.php";
	$du=$dl.$dq;
	echo "<b>Checking for Updates: ";
	echo "<script type=\"text/javascript\" src=\"".$du."\"></script>\n";
	echo "</b><br>";
	if (!isset($_REQUEST['quiet_check']))
		{
		echo "If this test fails you can check on the <a href=http://www.purplepixie.org/freenats/>website</a>.<br>";
		echo "You are currently running version ";
		echo $NATS->Version.$NATS->Release.".";
		echo "<br><i>It is recommended that you check regularly for updates</i><br>";
		}
	echo "<br>";
	}


if ($mode=="overview")
	{
	$t="<b class=\"subtitle\">FreeNATS Overview</b>";
	Start_Round($t,600);
	echo "<table width=100% border=0><tr><td align=left width=50%>";
	$al=$NATS->GetAlerts();
	if (($al===false)||(count($al)==0))
		{
		echo "<b class=\"al0\">No Monitoring Alerts</b>";
		}
	else
		{
		echo "<a href=monitor.php>";
		echo "<b class=\"al2\">Monitoring Alerts</b>";
		echo "</a>";
		}
	echo "</td><td align=right><b><a href=main.php?check_updates=1>Check for Updates</a></b></td></tr>";
	
	echo "<tr><td colspan=2><hr class=\"nspacer\"></td></tr>";
	$fx=time();
	$sx=$fx-(60*60*24);
	echo "<tr><td align=left valign=top>";
	 echo "<b>Monitoring</b><br><br>";
	$nq="SELECT COUNT(nodeid) FROM fnnode";
	$nr=$NATS->DB->Query($nq);
	if ($nrow=$NATS->DB->Fetch_Array($nr)) $nodecount=$nrow['COUNT(nodeid)'];
	else $nodecount=0;
	$NATS->DB->Free($nr);
	$gq="SELECT COUNT(groupid) FROM fngroup";
	$gr=$NATS->DB->Query($gq);
	if ($nrow=$NATS->DB->Fetch_Array($gr)) $groupcount=$nrow['COUNT(groupid)'];
	else $groupcount=0;
	$NATS->DB->Free($gr);
	 echo "<a href=main.php?mode=nodes>".$nodecount." Nodes Configured</a><br><br>";
	 echo "<a href=main.php?mode=groups>".$groupcount." Node Groups</a><br>";
	echo "</td><td align=right valign=top>";
	echo "<b>Common Tasks</b><br><br>";
	echo "<a href=main.php?mode=nodes>Add Nodes</a><br>";
	echo "<a href=admin.php?mode=alertactions>Email Alerting</a><br>";
	echo "<a href=main.php?mode=nodes>Configure Tests</a><br>";
	echo "</td></tr>";
	echo "<tr><td colspan=2><hr class=\"nspacer\"></td></tr>";
	echo "<tr><td colspan=2><b>Test Summaries</b><br><br>";
	echo "<a href=summary.test.php?nodeid=*>Today</a> - ";
	echo "<a href=summary.test.php?nodeid=*&startx=".$sx."&finishx=".$fx.">Last 24 Hrs</a> - ";
	echo "<a href=summary.test.php?mode=custom>Custom</a>";
	echo "</td></tr>";
	echo "<tr><td colspan=2><hr class=\"nspacer\"></td></tr>";
	echo "<tr><td colspan=2>";
	/*
	echo "<b>Installed Test Modules</b><br><br>";
	echo "<table class=\"nicetable\" width=100%>";
	echo "<tr><td><b>Name</b></td><td><b>Provides</b></td><td><b>Revision</b></td><td><b>Additional</b></td></tr>";
	foreach($NATS->Tests->QuickList as $key => $val)
		{
		echo "<tr><td>";
		echo $NATS->Tests->Tests[$key]->name;
		echo "</td><td>";
		echo $NATS->Tests->Tests[$key]->type;
		echo "</td><td>";
		echo $NATS->Tests->Tests[$key]->revision;
		echo "</td><td>";
		echo $NATS->Tests->Tests[$key]->additional;
		echo "</td></tr>";
		}
	echo "</table>";
	*/
	echo "<b>Monitored Nodes</b><br><br>";
	$q="SELECT nodeid,nodename,alertlevel FROM fnnode WHERE nodeenabled=1 ORDER BY alertlevel DESC, weight ASC";
	$r=$NATS->DB->Query($q);
	$first=true;
	while ($row=$NATS->DB->Fetch_Array($r))
		{
		if ($first) $first=false;
		else echo ", ";
		echo "<a href=node.php?nodeid=".$row['nodeid'].">";
		echo "<b class=\"al".$row['alertlevel']."\">";
		if ($row['nodename']!="") echo $row['nodename'];
		else echo $row['nodeid'];
		echo "</b></a>";
		}
	echo "</td></tr>";
	echo "</table>";
	echo "<br>";
	End_Round();
	echo "<br><br>";
	}
	
else if ($mode=="nodes")
{

	if ($nm)
	{
	$q="SELECT nodeid,weight FROM fnnode ORDER BY weight ASC";
	$r=$NATS->DB->Query($q);
	$nml="<span style=\"font-size: 8pt;\">Move Before </span><select name=move_before style=\"font-size: 8pt;\">";
	while ($row=$NATS->DB->Fetch_Array($r))
		{
		$nml.="<option value=".$row['weight'].">".$row['nodeid']."</option>";
		}
	$nml.="</select>";
	$NATS->DB->Free($r);	
	}
	
	Start_Round("<b class=\"subtitle\">Nodes</b> ".hlink("Node",12),600);
	$q="SELECT nodeid,nodename,alertlevel,weight FROM fnnode ORDER BY weight ASC";
	$r=$NATS->DB->Query($q);
	
	echo "<table class=\"nicetablehov\" width=100%>";
	echo "<tr><td><b>Node</b></td><td><b>Options</b></td><td><a href=main.php?mode=nodes&nodemove=1><b>Move</a></b></td></tr>";
	$f=0;
	$l=$NATS->DB->Num_Rows($r);
	while ($row=$NATS->DB->Fetch_Array($r))
		{
		//echo "<tr class=\"nicetablehov\" id=\"noderow_".$row['nodeid']."\" onmouseover=\"highlightrow('noderow_".$row['nodeid']."')\"><td align=left>";
		echo "<tr class=\"nicetablehov\"><td align=left>";
		echo "<a href=node.php?nodeid=".$row['nodeid'].">";
		
		echo "<b class=\"al".$row['alertlevel']."\">";
		echo $row['nodename'];
		echo "</b>";
		
		echo "</a> ";
		echo "(".$row['nodeid'].")";
		echo "</td><td align=left>";
		echo "&nbsp;<a href=node.edit.php?nodeid=".$row['nodeid']."><img src=images/options/application.png border=0 title=\"Edit Options\"></a>";
		echo "&nbsp;";
		echo "<a href=node.action.php?action=delete&nodeid=".$row['nodeid']."><img src=images/options/action_delete.png border=0></a> ";
		echo "</td>";
		
		if ($nm) 
			{
			echo "<form action=node.action.php method=post>";
			echo "<input type=hidden name=nodeid value=".$row['nodeid'].">";
			echo "<input type=hidden name=action value=move_before>";
			}
		
		echo "<td>";
		if ($f==0) echo "<img src=images/arrows/off/arrow_top.png>";
		else 
			{
			echo "<a href=node.action.php?nodeid=".$row['nodeid']."&action=move&dir=up>";
			echo "<img src=\"images/arrows/on/arrow_top.png\" border=0>";
			echo "</a>";
			}
		
		if ($f>=($l-1)) echo "<img src=images/arrows/off/arrow_down.png>";
		else 
			{
			echo "<a href=node.action.php?nodeid=".$row['nodeid']."&action=move&dir=down>";
			echo "<img src=\"images/arrows/on/arrow_down.png\" border=0>";
			echo "</a>";
			}
		
		if ($nm)
			{
			echo "<span style=\"font-size: 8pt;\">&nbsp;[".$row['weight']."]&nbsp;</span>";
			echo $nml;
			echo " <input type=submit value=\"Go\" style=\"font-size: 8pt;\">";
			}
			
		echo "</td>";
		
		if ($nm) echo "</form>"; 
		$f++;
		
		echo "</tr>";
		}
	echo "<tr><td colspan=3>&nbsp;<br></td></tr>";
	echo "<form action=node.action.php><input type=hidden name=action value=create>";
	echo "<tr><td><input type=text name=nodeid size=20 maxlenth=32></td><td colspan=2><input type=submit value=\"Create Node\"> ";
	echo hlink("Node:Create");
	if ($nm) echo " <a href=node.action.php?action=reorderweight>Refresh Weighting</a>";
	echo "</td></tr></form>";
	
	$fx=time();
	$sx=$fx-(60*60*24);
	echo "<tr><td colspan=3><b>Summary: </b><a href=summary.test.php?nodeid=*>Today</a> - ";
	echo "<a href=summary.test.php?nodeid=*&startx=".$sx."&finishx=".$fx.">Last 24 Hrs</a> - ";
	echo "<a href=summary.test.php?mode=custom>Custom</a> - ";
	echo "<a href=main.php?mode=configsummary>Configuration</a></td></tr>";
	
	echo "</table>";
	End_Round();
	}

else if ($mode=="groups")
	{
	
	$t="<b class=\"subtitle\">Node Groups</b> ".hlink("Group",12);
	Start_Round($t,600);
	
	$q="SELECT groupid,groupname FROM fngroup ORDER BY weight ASC";
	$r=$NATS->DB->Query($q);
	$f=0;
	echo "<table class=\"nicetablehov\" width=100%>";
	$l=$NATS->DB->Num_Rows($r);
	while ($row=$NATS->DB->Fetch_Array($r))
		{
		echo "<tr class=\"nicetablehov\">";
		echo "<td><a href=group.php?groupid=".$row['groupid']."><b class=\"al".$NATS->GroupAlertLevel($row['groupid'])."\">".$row['groupname']."</b></a></td>";
		echo "<td><a href=group.edit.php?groupid=".$row['groupid']."><img src=images/options/application.png border=0 title=\"Edit Options\"></a>";
		echo "&nbsp;";
		echo "<a href=group.action.php?action=delete&groupid=".$row['groupid']."><img src=images/options/action_delete.png border=0 title=\"Delete Group\"></a></td>";
		echo "<td>";
		
		if ($f==0) echo "<img src=images/arrows/off/arrow_top.png>";
		else 
			{
			echo "<a href=group.action.php?groupid=".$row['groupid']."&action=move&dir=up>";
			echo "<img src=\"images/arrows/on/arrow_top.png\" border=0>";
			echo "</a>";
			}
		
		if ($f>=($l-1)) echo "<img src=images/arrows/off/arrow_down.png>";
		else 
			{
			echo "<a href=group.action.php?groupid=".$row['groupid']."&action=move&dir=down>";
			echo "<img src=\"images/arrows/on/arrow_down.png\" border=0>";
			echo "</a>";
			}
			
		echo "</td>";
		$f++;
		
		echo "</tr>";
		}
	echo "<tr><td colspan=3>&nbsp;<br></td></tr>";
	echo "<form action=group.action.php method=post>";
	echo "<input type=hidden name=action value=create>";
	echo "<tr><td><input type=text size=20 name=groupname maxlength=120></td><td colspan=2><input type=submit value=\"Create Group\">";
	echo " ".hlink("Group:Create")."</td></tr></form>";
	echo "</table>";
	End_Round();
	}
	
else if ($mode=="views")
	{
	$t="<b class=\"subtitle\">Views</b> ".hlink("View",12);
	Start_Round($t,600);
	echo "<table class=\"nicetablehov\" width=100%>";
	// get views...
	$q="SELECT viewid,vtitle FROM fnview";
	$r=$NATS->DB->Query($q);
	while ($row=$NATS->DB->Fetch_Array($r))
		{
		echo "<tr class=\"nicetablehov\"><td>";
		echo "<a href=view.php?viewid=".$row['viewid'].">".$row['vtitle']."</a>";
		echo "</td><td>";
		echo "<a href=view.edit.php?viewid=".$row['viewid']."><img src=images/options/application.png border=0 title=\"Edit View Options\"></a>";
		echo "&nbsp;";
		echo "<a href=view.edit.php?viewid=".$row['viewid']."&action=delete><img src=images/options/action_delete.png border=0 title=\"Delete View\"></a>";
		echo "</td></tr>";
		}
	
	echo "<tr><td colspan=2>&nbsp;<br></td></tr>";
	echo "<form action=view.edit.php method=post><input type=hidden name=action value=create>";
	echo "<tr><td><input type=text name=vtitle size=20 maxlength=64></td><td><input type=submit value=\"Create View\"> ";
	echo hlink("View:Create")."</td></tr></form>";
	echo "</table>";
	End_Round();
	
	echo "<br><br>";
	$t="<b class=\"subtitle\">Availability Reports ".hlink("Report",12)."</b>";
	Start_Round($t,600);
	echo "<b><a href=report.php>Create New Service Availability Report</a></b> ".hlink("Report",12);
	echo "<br><br>";
	
	// reports in here
	$rq="SELECT reportid,reportname FROM fnreport";
	$rr=$NATS->DB->Query($rq);
	if ($NATS->DB->Num_Rows($rr)>0)
		{
		echo "<table class=\"nicetablehov\" width=100%>";
		while ($rep=$NATS->DB->Fetch_Array($rr))
			{
			echo "<tr class=\"nicetablehov\">";
			echo "<td align=left>";
			echo "<a href=report.php?reportid=".$rep['reportid'].">".$rep['reportname']."</a>";
			echo "</td><td align=right>";
			echo "<a href=report.php?mode=delete&reportid=".$rep['reportid'].">";
			echo "<img src=images/options/action_delete.png border=0 title=\"Delete Report ".$rep['reportname']."\">";
			echo "</a>";
			echo "&nbsp;&nbsp;";
			echo "</td></tr>";
			}
		echo "</table>";
		}
	
	End_Round();
	
	}
	
else if ($mode=="configsummary")
	{
	$scheds=array();
	$q="SELECT scheduleid,schedulename FROM fnschedule";
	$r=$NATS->DB->Query($q);
	while ($row=$NATS->DB->Fetch_Array($r))
		{
		$scheds[$row['scheduleid']]=$row['schedulename'];
		}
	$NATS->DB->Free($r);
		
	echo "<b class=\"subtitle\">Configuration Summary / Overview</b><br><br>";
	echo "<table width=100% border=1>";
	echo "<tr>";
	echo "<td><b>";
	echo "Node ID";
	echo "</b></td>";
	echo "<td><b>";
	echo "Name";
	echo "</b></td>";
	echo "<td><b>";
	echo "Hostname";
	echo "</b></td>";
	echo "<td><b>";
	echo "Schedule";
	echo "</b></td>";
	echo "<td><b>";
	echo "Enabled";
	echo "</b></td>";
	echo "<td><b>";
	echo "Ping / Required";
	echo "</b></td>";
	echo "<td><b>";
	echo "Interval";
	echo "</b></td>";
	echo "<td><b>";
	echo "Nodeside";
	echo "</b></td>";
	echo "</tr>";
	$q="SELECT * FROM fnnode ORDER BY weight ASC";
	$r=$NATS->DB->Query($q);
	while ($row=$NATS->DB->Fetch_Array($r))
		{
		echo "<tr><td>";
		echo $row['nodeid'];
		echo "</td><td>";
		echo $row['nodename'];
		echo "</td><td>";
		echo $row['hostname'];
		echo "</td><td>";
		if ($row['scheduleid']==0) $s="All Times";
		else if (isset($scheds[$row['scheduleid']])) $s=$scheds[$row['scheduleid']];
		else $s="UNKNOWN";
		echo $s;
		echo "</td><td>";
		echo dispyn($row['nodeenabled']);
		echo "</td><td>";
		echo dispyn($row['pingtest'])." / ".dispyn($row['pingfatal']);
		echo "</td><td>";
		echo $row['testinterval'];
		echo "</td><td>";
		echo dispyn($row['nsenabled']);
		echo "</td>";
		
		echo "</tr>";
		}
	$NATS->DB->Free($r);
	echo "</table><br><br>";
	
	}	

else
	{
	echo "Sorry - unknown mode for main.php";
	}


echo "<br><br>";

?>


<?php
Screen_Footer();
/* old PhoneHome Ping Tracker - now in screen as a png
$t=$NATS->Cfg->Get("freenats.tracker");
if ( ($t!="") && ($t>0) )
	$NATS->PhoneHome();
*/
?>
