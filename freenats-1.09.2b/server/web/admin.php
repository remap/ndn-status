<?php
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008-2010 PurplePixie Systems

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
if ($NATS_Session->userlevel<9) UL_Error("Admin Interface");

if (isset($_REQUEST['action']))
	{
	switch($_REQUEST['action'])
		{
		case "save_user":
		$q="UPDATE fnuser SET realname=\"".ss($_REQUEST['realname'])."\",userlevel=".ss($_REQUEST['userlevel']);
		if ((isset($_REQUEST['pword']))&&($_REQUEST['pword']!="_NOTTHIS_")) $q.=",password=MD5(\"".ss($_REQUEST['pword'])."\")";
		$q.=" WHERE username=\"".ss($_REQUEST['username'])."\"";
		$NATS->DB->Query($q);
		if ($NATS->DB->Affected_Rows()<=0) $amsg="Save User Failed or Nothing Changed";
		else $amsg="Save User ".$_REQUEST['username']." Succeeded";
		break;
		
		case "create_user":
		$q="INSERT INTO fnuser(username,password,realname,userlevel) VALUES(\"".ss($_REQUEST['username'])."\",";
		$q.="MD5(\"".ss($_REQUEST['pword'])."\"),\"".ss($_REQUEST['realname'])."\",".ss($_REQUEST['userlevel']).")";
		$NATS->DB->Query($q);
		//echo $q;
		if ($NATS->DB->Affected_Rows()<=0) $amsg="Create User Failed";
		else $amsg="User ".$_REQUEST['username']." Created";
		break;
		
		case "delete_user": 
		/* - disabled for 0.02.44 to allow duplicate deletion
		if ($_REQUEST['username']=="admin")
			{
			$amsg="Can't delete the admin user";
			break;
			}
		*/
		if (!isset($_REQUEST['confirmed']))
			{
			$back=urlencode("admin.php?action=delete_user&mode=users&username=".$_REQUEST['username']."&confirmed=1");
			$url="confirm.php?action=Delete+User+".$_REQUEST['username']."&back=".$back;
			header("Location: ".$url);
			exit();
			}
		$q="DELETE FROM fnuser WHERE username=\"".ss($_REQUEST['username'])."\"";
		$NATS->DB->Query($q);
		$amsg="User ".$_REQUEST['username']." Deleted";
		break;
		
		case "var_save":
		if ($_REQUEST['new_var']=="") // delete
			{
			$q="DELETE FROM fnconfig WHERE fnc_var=\"".ss($_REQUEST['orig_var'])."\"";
			}
		else // update
			{
			$q="UPDATE fnconfig SET fnc_var=\"".ss($_REQUEST['new_var'])."\",fnc_val=\"".ss($_REQUEST['new_val'])."\" ";
			$q.="WHERE fnc_var=\"".ss($_REQUEST['orig_var'])."\"";
			}
		$NATS->DB->Query($q);
		if ($NATS->DB->Affected_Rows()<=0) $amsg="Update/Delete Variable Failed";
		else $amsg="Updated/Deleted Variable";
		break;
		case "var_new":
		$q="INSERT INTO fnconfig(fnc_var,fnc_val) VALUES(\"".ss($_REQUEST['new_var'])."\",\"".ss($_REQUEST['new_val'])."\")";
		//echo $q;
		$NATS->DB->Query($q);
		if ($NATS->DB->Affected_Rows()<=0) $amsg="Create Variable Failed";
		else $amsg="Created Variable";
		break;
		
		case "save_aa":
		$q="UPDATE fnalertaction SET ";
		$q.="atype=\"".ss($_REQUEST['atype'])."\",";
		$q.="ctrlimit=".ss($_REQUEST['ctrlimit']).",";
		$q.="ctrtoday=".ss($_REQUEST['ctrtoday']).",";
		$q.="aname=\"".ss($_REQUEST['aname'])."\",";
		$q.="scheduleid=".ss($_REQUEST['scheduleid']).",";
		if (isset($_REQUEST['efrom'])) $q.="efrom=\"".ss($_REQUEST['efrom'])."\",";
		$q.="etolist=\"".ss($_REQUEST['etolist'])."\",";
		if (isset($_REQUEST['esubject'])) $q.="esubject=".ss($_REQUEST['esubject']).",";
		$q.="etype=".ss($_REQUEST['etype']);
		if (isset($_REQUEST['awarnings'])) $q.=",awarnings=".ss($_REQUEST['awarnings']);
		else $q.=",awarnings=0";
		if (isset($_REQUEST['adecrease'])) $q.=",adecrease=".ss($_REQUEST['adecrease']);
		else $q.=",adecrease=0";
		$q.=" WHERE aaid=".ss($_REQUEST['aaid']);
		//echo $q;
		$NATS->DB->Query($q);
		if ($NATS->DB->Affected_Rows()<=0) $amsg="Action Update Failed or Nothing Changed";
		else $amsg="Action Updated";
		break;
		
		case "action_test":
		$q="SELECT mdata FROM fnalertaction WHERE aaid=".ss($_REQUEST['aaid'])." LIMIT 0,1";
		$r=$NATS->DB->Query($q);
		$row=$NATS->DB->Fetch_Array($r);
		$oldm=$row['mdata'];
		$q="UPDATE fnalertaction SET mdata=\"** ACTION TEST **\" WHERE aaid=".ss($_REQUEST['aaid']);
		$NATS->DB->Query($q);
		$NATS->ActionFlush();
		$q="UPDATE fnalertaction SET mdata=\"".ss($oldm)."\" WHERE aaid=".ss($_REQUEST['aaid']);
		$NATS->DB->Query($q);
		$amsg="Alert Action Tested &amp; Flushed";
		break;
		
		case "action_create":
		$q="INSERT INTO fnalertaction(atype) VALUES(\"\")";
		$NATS->DB->Query($q);
		$amsg="Created New Alert Action";
		$_REQUEST['aaid']=$NATS->DB->Insert_Id();
		break;
		
		case "action_delete":
		if (!isset($_REQUEST['confirmed']))
			{
			$back=urlencode("admin.php?mode=alertactions&aaid_del=".$_REQUEST['aaid_del']."&action=action_delete&confirmed=1");
			$url="confirm.php?action=Delete+alert+action&back=".$back;
			header("Location: ".$url);
			exit();
			}
		// otherwise confirmed
		$q="DELETE FROM fnalertaction WHERE aaid=".ss($_REQUEST['aaid_del']);
		$NATS->DB->Query($q);
		if ($NATS->DB->Affected_Rows()>0) $amsg="Alert Action Deleted";
		else $amsg="Alert Action Delete Failed";
		$q="DELETE FROM fnnalink WHERE aaid=".ss($_REQUEST['aaid_del']);
		$NATS->DB->Query($q);
		break;
		
		case "optimize":
		$q="OPTIMIZE TABLE ".ss($_REQUEST['table']);
		$NATS->DB->Query($q);
		$msg="Optimised Table ".$_REQUEST['table'];
		break;
		
		}
	}

ob_end_flush();
Screen_Header("Administration Interface",1,1,"","main","admin");

if (isset($_REQUEST['mode'])) $mode=$_REQUEST['mode'];
else $mode="";

if (isset($_REQUEST['message'])) echo "<br><b>".$_REQUEST['message']."</b><br>";
if (isset($amsg)) echo "<br><b>".$amsg."</b><br>";

echo "<br>";
if (isset($_REQUEST['updatecheck']))
	{
	// check for updates
	$dq="?CheckVersion=".$NATS->Version."&JSMode=1";
	$dl="http://www.purplepixie.org/freenats/download.php";
	$du=$dl.$dq;
	/* old method
	$cp=@fopen($du,"r");
	if ($cp>0)
		{
		$cs=@fgets($cp,128);
		@fclose($cp);
		if ($cs=="0") echo "System Up to Date<br>";
		else echo "Update Available: <a href=http://www.purplepixie.org/freenats>".$cs."</a><br>";
		}
	else echo "Error Checking for Updates<br>";
	*/
	echo "Checking Version: ";
	echo "<script type=\"text/javascript\" src=\"".$du."\"></script>\n";
	echo "<br><br>";
	}
else if ($mode=="")
	{
	echo "<a href=admin.php?updatecheck=1><b>Check for FreeNATS Updates</b></a><br><br>";
	}
	
function tul($l)
{
if ($l>9) return "Administrator";
if ($l>4) return "Power User";
if ($l>0) return "Normal User";
return "Disabled";
}

function aat_etype($type)
{
switch ($type)
	{
	case 0: return "Short";
	case 1: return "Long";
	default: return "Unknown";
	}
}

function aat_esub($type)
{
switch ($type)
	{
	case 0: return "Blank";
	case 1: return "Short";
	case 2: return "Long";
	default: return "Unknown";
	}
}

function aat_atype($type)
{
switch($type)
	{
	case "": case "Disabled": return "Disabled";
	case "email": return "EMail";
	case "url": return "URL";
	case "mqueue": return "Message Queue";
	default: return "Unknown (".$type.")";
	}
}

if ($mode=="users")
{
echo "<b class=\"subtitle\">Users</b><br><br>";




$q="SELECT username,realname,userlevel FROM fnuser";
$r=$NATS->DB->Query($q);
echo "<table border=0>";
echo "<tr><td><b>Username&nbsp;</b></td>";
echo "<td><b>Real Name</b></td><td><b>User Level</b></td><td><b>Password</b></td><td><b>Options</b></td></tr>";
while ($row=$NATS->DB->Fetch_Array($r))
	{
	echo "<form action=admin.php method=post>";
	echo "<input type=hidden name=action value=save_user>";
	echo "<input type=hidden name=mode value=users>";
	echo "<input type=hidden name=username value=\"".$row['username']."\">";
	echo "<tr><td>".$row['username']."</td>";
	echo "<td><input type=text name=realname value=\"".$row['realname']."\" size=20 maxlength=120></td>";
	echo "<td><select name=userlevel>";
	echo "<option value=".$row['userlevel'].">".tul($row['userlevel'])."</option>";
	echo "<option value=0>".tul(0)."</option>";
	echo "<option value=1>".tul(1)."</option>";
	echo "<option value=5>".tul(5)."</option>";
	echo "<option value=10>".tul(10)."</option>";
	echo "</select>";
	echo "</td>";
	echo "<td><input type=password name=pword value=\"_NOTTHIS_\" size=10 maxlength=128></td>";
	echo "<td><input type=submit value=\"Save\"> <a href=admin.php?action=delete_user&username=".$row['username'].">Delete</a></td>";
	echo "</tr>";
	echo "</form>";
	}
echo "<form action=admin.php method=post>";
echo "<input type=hidden name=action value=create_user>";
echo "<input type=hidden name=mode value=users>";
echo "<tr><td><input type=text name=username size=20 maxlength=60></td>";
echo "<td><input type=text name=realname size=20 maxlength=120></td>";
echo "<td><select name=userlevel>";
echo "<option value=1>".tul(1)."</option>";
echo "<option value=0>".tul(0)."</option>";
echo "<option value=5>".tul(5)."</option>";
echo "<option value=10>".tul(10)."</option>";
echo "</select></td>";
echo "<td><input type=password name=pword size=10 maxlength=60></td>";
echo "<td><input type=submit value=\"Create User\"></td>";
echo "</tr></form>";
echo "</table><br>";
echo "<br>";
$NATS->DB->Free($r);
}
else if ($mode=="nodetestsessions")
{
echo "<b class=\"subtitle\">Test Sessions for ".$_REQUEST['nodeid']."</b><br><br>";
echo "<b>Running Sessions for ".$_REQUEST['nodeid']."</b><br>";
$q="SELECT * FROM fntestrun WHERE fnode=\"".ss($_REQUEST['nodeid'])."\" AND finishx=0 ORDER BY trid DESC";
$r=$NATS->DB->Query($q);
if ($NATS->DB->Num_Rows($r)==0) echo "<i>No running test sessions</i><br>";
echo "<table border=0>";
while ($row=$NATS->DB->Fetch_Array($r))
	{
	echo "<tr><td><a href=testrun.php?trid=".$row['trid'].">run/".$row['trid']."</a></td>";
	echo "<td>".nicedt($row['startx'])." - ";
	if ($row['finishx']>0) echo nicedt($row['finishx']);
	else echo "Still Running";
	echo " (<a href=log.php?f_entry=Tester+".$row['trid'].">System Logs</a>)";
	echo "</td></tr>";
	}
echo "</table>";
$NATS->DB->Free($r);
echo "<br>";
echo "<br>";

echo "<b>Last 100 Previous Sessions for ".$_REQUEST['nodeid']."</b><br>";
$q="SELECT * FROM fntestrun WHERE fnode=\"".ss($_REQUEST['nodeid'])."\" ORDER BY trid DESC LIMIT 0,100";
$r=$NATS->DB->Query($q);
if ($NATS->DB->Num_Rows($r)==0) echo "<i>No previous test sessions</i><br>";
echo "<table border=0>";
while ($row=$NATS->DB->Fetch_Array($r))
	{
	echo "<tr><td><a href=testrun.php?trid=".$row['trid'].">run/".$row['trid']."</a></td>";
	echo "<td>".nicedt($row['startx'])." - ";
	if ($row['finishx']>0) echo nicedt($row['finishx']);
	else echo "Still Running";
	echo " (<a href=log.php?f_entry=Tester+".$row['trid'].">System Logs</a>)";
	echo "</td></tr>";
	}
echo "</table>";
$NATS->DB->Free($r);
echo "<br>";
echo "<br>";

}
else if ($mode=="testsessions")
{
echo "<b class=\"subtitle\">Running Test Sessions</b><br><br>";
$q="SELECT * FROM fntestrun WHERE finishx=0 ORDER BY trid DESC";
$r=$NATS->DB->Query($q);
if ($NATS->DB->Num_Rows($r)==0) echo "<i>No running test sessions</i><br>";
echo "<table border=0>";
while ($row=$NATS->DB->Fetch_Array($r))
	{
	echo "<tr><td><a href=testrun.php?trid=".$row['trid'].">run/".$row['trid']."</a></td>";
	echo "<td>".nicedt($row['startx'])." - ";
	if ($row['finishx']>0) echo nicedt($row['finishx']);
	else echo "Still Running";
	echo " (<a href=log.php?f_entry=Tester+".$row['trid'].">System Logs</a>)";
	echo "</td></tr>";
	}
echo "</table>";
$NATS->DB->Free($r);
echo "<br>";
echo "<form action=admin.php method=post>";
echo "<input type=hidden name=mode value=nodetestsessions>";
echo "<b>Last 100 Test Sessions for </b>";
$q="SELECT nodeid,nodename FROM fnnode ORDER BY weight ASC";
$r=$NATS->DB->Query($q);
echo "<select name=nodeid>";
while ($row=$NATS->DB->Fetch_Array($r))
	{
	echo "<option value=".$row['nodeid'].">".$row['nodename']." (".$row['nodeid'].")</option>";
	}
echo "</select> <input type=submit value=Go></form><br>";
}
else if ($mode=="alertactions")
{
echo "<b class=\"subtitle\">Alert Actions</b><br><br>";

if (isset($_REQUEST['aaid']))
	{ // view/edit aaid
	$q="SELECT * FROM fnalertaction WHERE aaid=".ss($_REQUEST['aaid']);
	$r=$NATS->DB->Query($q);
	if (!$row=$NATS->DB->Fetch_Array($r))
		{
		echo "<b>Error Fetching AAID</b><br><br>";
		Screen_Footer();
		exit();
		}
	echo "<table border=0>";
	echo "<form action=admin.php method=post>";
	echo "<input type=hidden name=action value=save_aa>";
	echo "<input type=hidden name=mode value=alertactions>";
	echo "<input type=hidden name=aaid value=".$_REQUEST['aaid'].">";
	echo "<tr><td>ID : </td><td>action/".$_REQUEST['aaid']."</td></tr>";
	
	echo "<tr><td>Action Name : </td>";
	echo "<td>";
	echo "<input type=text name=aname size=30 maxlength=120 value=\"".$row['aname']."\">";
	echo "</td></tr>";
	
	echo "<tr><td>Type : </td><td>";
	echo "<select name=atype>";
	echo "<option value=".$row['atype'].">".aat_atype($row['atype'])."</option>";
	echo "<option value=Disabled>Disabled</option>";
	echo "<option value=email>EMail</option>";
	echo "<option value=url>URL</option>";
	echo "<option value=mqueue>Message Queue</option>";
	echo "</select>";
	echo "</td></tr>";
	
	echo "<tr><td>Schedule : </td><td>";
	echo "<select name=scheduleid>";
	echo "<option value=0>At All Times</option>";
	$sq="SELECT scheduleid,schedulename FROM fnschedule";
	$sr=$NATS->DB->Query($sq);
	while ($sched=$NATS->DB->Fetch_Array($sr))
		{
		if ($sched['scheduleid']==$row['scheduleid']) $s.=" selected";
		else $s="";
		echo "<option value=".$sched['scheduleid'].$s.">".$sched['schedulename']."</option>";
		}
	echo "</select>";
	$NATS->DB->Free($sr);
	echo " ".hlink("AlertSchedule");
	echo "</td></tr>";
	
	
	echo "<tr><td>Warnings : </td>";
	if ($row['awarnings']==1) $s=" checked";
	else $s="";
	echo "<td><input type=checkbox name=awarnings value=1".$s."> ".hlink("AAction:Warnings")."</td></tr>";
	
	echo "<tr><td>Decreases : </td>";
	if ($row['adecrease']==1) $s=" checked";
	else $s="";
	echo "<td><input type=checkbox name=adecrease value=1".$s."> ".hlink("AAction:Decreases")."</td></tr>";
	
	echo "<tr><td>Action Limit : </td>";
	echo "<td>";
	echo "<input type=text name=ctrlimit size=3 maxlength=6 value=\"".$row['ctrlimit']."\"> ";
	echo hlink("AAction:Limit");
	echo "</td></tr>";
	
	echo "<tr><td>Action Counter : </td>";
	echo "<td>";
	echo "<input type=text name=ctrtoday size=3 maxlength=6 value=\"".$row['ctrtoday']."\"> ";
	echo hlink("AAction:Counter");
	echo " (for ";
	if ($row['ctrdate']=="") echo "<i>unknown</i>";
	else echo substr($row['ctrdate'],6,2)."/".substr($row['ctrdate'],4,2)."/".substr($row['ctrdate'],0,4);
	echo ")";
	echo "</td></tr>";
	
if ($row['atype']!="url")
	{
	
	echo "<tr><td>Email From : </td>";
	echo "<td>";
	echo "<input type=text name=efrom size=30 maxlength=120 value=\"".$row['efrom']."\">";
	echo "</td></tr>";
	
	echo "<tr><td>Email Subject : </td><td>";
	echo "<select name=esubject>";
	echo "<option value=".$row['esubject'].">".aat_esub($row['esubject'])."</option>";
	echo "<option value=0>Blank</option>";
	echo "<option value=1>Short</option>";
	echo "<option value=2>Long</option>";
	echo "</select>";
	echo "</td></tr>";
	
	}
	
	echo "<tr><td>Msg Type : </td><td>";
	echo "<select name=etype>";
	echo "<option value=".$row['etype'].">".aat_etype($row['etype'])."</option>";
	echo "<option value=0>Short</option>";
	echo "<option value=1>Long</option>";
	echo "</select>";
	echo "</td></tr>";
	
	echo "<tr><td valign=top>Email To<br>or URL : </td><td>";
	echo "<textarea name=etolist cols=40 rows=6>".$row['etolist']."</textarea>";
	echo "</td></tr>";
	
	echo "<tr><td colspan=2><input type=submit value=\"Update Action\"> &nbsp; <a href=admin.php>Cancel Update</a> | ";
	echo "<a href=admin.php?aaid=".$_REQUEST['aaid']."&mode=alertactions&action=action_test>Test Action</a> | ";
	echo "<a href=admin.php?aaid_del=".$_REQUEST['aaid']."&mode=alertactions&action=action_delete>Delete Action</a>";
	echo "</td></tr>";
	
	echo "</form></table><br><br>";
	$NATS->DB->Free($r);
	}

$q="SELECT aaid,atype,aname FROM fnalertaction";
$r=$NATS->DB->Query($q);
echo "<table class=\"nicetable\"><tr>";
echo "<td><b>ID</b></td><td><b>Action Name</b></td><td><b>Action Type</b></td></tr>";
while ($row=$NATS->DB->Fetch_Array($r))
	{
	echo "<tr><td>";
	echo "<a href=admin.php?mode=alertactions&aaid=".$row['aaid'].">".$row['aaid']."</a></td>";
	echo "<td><a href=admin.php?mode=alertactions&aaid=".$row['aaid'].">".$row['aname']."</a></td><td>";
	echo aat_atype($row['atype']);
	echo "</td></tr>";
	}
echo "</table>";

echo "<br><a href=admin.php?mode=alertactions&action=action_create><b>Create New Alert Action</b></a><br>";
echo "<br><br>";
}
else if ($mode=="logs")
{
echo "<b class=\"subtitle\">System Logs</b><br><br>";
echo "<a href=log.php>System Event Log</a><br><br>";

echo "<br><br>";
}
else if ($mode=="status")
{
// system healthcheck

// usage data
//$q="SELECT COUNT(fnnode.nodeid),COUNT(fngroup.groupid),COUNT( FROM fnnode,fngroup";
$q="SHOW TABLE STATUS LIKE \"fn%\"";
$r=$NATS->DB->Query($q);

echo "<b class=\"maintitle\">FreeNATS System Status</b><br><br>";

$tinfo=array();

echo "<b class=\"subtitle\">Table Sizes</b><br><br>";

echo "<table class=\"nicetable\">";
echo "<tr><td><b>Table Name</b></td><td><b>Size (Rows)</b></td><td><b>Size (kb)</b></td><td><b>Other Information</b></td></tr>";
while ($row=$NATS->DB->Fetch_Array($r))
	{
	echo "<tr><td>".$row['Name']."</td><td>".$row['Rows']."</td>";
	echo "<td>".round($row['Data_length']/1024,0)."</td><td>";
	if (isset($tinfo[$row['Name']])) echo $tinfo[$row['Name']];
	else echo "&nbsp;";
	if ($row['Data_free']>0)
		{
		echo " [".round($row['Data_free']/1024,0)."kb Free - <a href=admin.php?mode=status&action=optimize&table=".$row['Name'].">Optimise</a>]";
		}
	echo "</td></tr>";
	}
echo "</table>";
$NATS->DB->Free($r);
echo "<br><br>";

echo "<b class=\"subtitle\">Test Sessions</b><br><br>";
$q="SELECT * FROM fntestrun WHERE finishx=0";
$r=$NATS->DB->Query($q);
echo "<b>".$NATS->DB->Num_Rows($r)." Open/Running Test Sessions</b><br><br>";
if ($NATS->DB->Num_Rows($r)>0)
	{
	echo "<table class=\"nicetable\">";
	echo "<tr><td><b>ID</b></td><td><b>Node</b></td><td><b>Started</b></td><td><b>Notes</b></td></tr>";
	$nowx=time();
	$fifteenx=$nowx-(15*60);
	$hourx=$nowx-(60*60);
	while ($row=$NATS->DB->Fetch_Array($r))
		{
		echo "<tr><td><a href=testrun.php?trid=".$row['trid'].">";
		echo $row['trid']."</a></td>";
		echo "<td><a href=node.php?nodeid=".$row['fnode']."</a></td>";
		echo "<td>".nicedt($row['startx'])." - ".dtago($row['startx'])."</td>";
		echo "<td>";
		if ($row['startx']<$hourx) echo "Error: Over an hour old";
		else if($row['startx']<$fifteenx) echo "Warning: Over fifteen minutes old";
		else echo "&nbsp;";
		echo "</td></tr>";
		}
	echo "</table>";
	}
$NATS->DB->Free($r);
echo "<br><br>";

echo "<b class=\"subtitle\">Environment: PHP Web (Apache Module/PHP-CGI)</b><br><br>";
$env_test_web=1;
include("environment.test.php");
echo "<br>";
echo "<b class=\"subtitle\">Environment: PHP CLI (Command-Line Binary)</b><br><br>";
$output=array();
$return=0;
exec("php -q environment.test.php",$output,$return);
if ($return!=1)
	{
	echo "<b style=\"color: red;\">Error: </b> PHP CLI Script did not seem to execute. PHP-CLI is required for FreeNATS (fatal error)<BR />";
	}
foreach($output as $line)
	echo $line;

echo "<br><br>";
echo "Please note this merely displays FreeNATS software status - not<br>the status of ";
echo "the underlying Virtual Machine (if applicable).<br><br> You should monitor disk space and ";
echo "performance for the VM via<br>the relevant system (such as the rPath interface).<br><br>";
	
}
else if ($mode=="variables")
{
echo "<b class=\"subtitle\">Variables</b> ".hlink("Variable")."<br><br>";
$q="SELECT * FROM fnconfig ORDER BY fnc_var ASC";
$r=$NATS->DB->Query($q);
echo "<table border=0>";
while ($row=$NATS->DB->Fetch_Array($r))
	{
	echo "<form action=admin.php method=post>";
	echo "<input type=hidden name=action value=var_save>";
	echo "<input type=hidden name=mode value=variables>";
	echo "<input type=hidden name=orig_var value=\"".$row['fnc_var']."\">";
	echo "<input type=hidden name=orig_val value=\"".$row['fnc_val']."\">";
	echo "<tr><td><input type=text size=20 maxlength=60 name=new_var value=\"".$row['fnc_var']."\"> ";
	echo "</td>";
	echo "<td>=</td>";
	echo "<td><input type=text size=20 maxlength=60 name=new_val value=\"".$row['fnc_val']."\"></td>";
	echo "<td><input type=submit value=\"Save\"> ";
	echo hlink("Var:".$row['fnc_var']);
	echo "</td>";
	echo "</tr>";
	echo "</form>";
	}
echo "<form action=admin.php method=post>";
echo "<input type=hidden name=action value=var_new>";
echo "<input type=hidden name=mode value=variables>";
echo "<tr><td><input type=text size=20 maxlength=60 name=new_var value=\"\"></td>";
echo "<td>=</td>";
echo "<td><input type=text size=20 maxlength=60 name=new_val value=\"\"></td>";
echo "<td><input type=submit value=\"Create\"></td>";
echo "</tr>";
echo "</form>";
echo "</table>";
}
else if ($mode=="sysinfo")
{
echo "<b class=\"subtitle\">FreeNATS System Information</b><br><br>";

echo "<b>Version Information</b><br><br>";
echo "<table border=0>";
echo "<tr><td>Version:</td><td>".$NATS->Version."</td></tr>";
echo "<tr><td>Release:</td><td>".$NATS->Release."</td></tr>";
echo "<tr><td>Compound:</td><td>".$NATS->Version.$NATS->Release."</td></tr>";
echo "</table><br><br>";

echo "<b>Registered Test Modules</b><br><br>";
echo "<table class=\"nicetable\" width=600>";
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
echo "</table><br><br>";

echo "<b>Registered Event Handlers</b><br><br>";
echo "<table class=\"nicetable\" width=600>";
echo "<tr><td><b>Event</b></td><td><b>Handler(s)</b></td></tr>";
foreach($NATS->EventHandlers as $key => $val)
	{
	echo "<tr><td valign=top>".$key."</td><td>";
	foreach($NATS->EventHandlers[$key] as $handler)
		echo $handler."<br>";
	echo "</td></tr>";
	}
echo "</table><br><br>";
}
else // catch-all
{
echo "<img src=images/options/user.png style=\"vertical-align: bottom;\">&nbsp;&nbsp;";
echo "<a href=admin.php?mode=users>User Administration</a><br><br>";
echo "<img src=images/options/application.png style=\"vertical-align: bottom;\">&nbsp;&nbsp;";
echo "<a href=log.php>System Event Log</a><br><br>";
echo "<img src=images/options/letter_open.png style=\"vertical-align: bottom;\">&nbsp;&nbsp;";
echo "<a href=admin.php?mode=alertactions>Alert Actions</a><br><br>";
echo "<img src=images/options/action_add.png style=\"vertical-align: bottom;\">&nbsp;&nbsp;";
echo "<a href=admin.php?mode=variables>System Variables</a><br><br>";
echo "<img src=images/options/file.png style=\"vertical-align: bottom;\">&nbsp;&nbsp;";
echo "<a href=admin.php?mode=testsessions>Test Sessions</a><br><br>";
echo "<img src=images/options/time.png style=\"vertical-align: bottom;\">&nbsp;&nbsp;";
echo "<a href=schedule.php>Test and Alert Schedules</a><br><br>";
echo "<img src=images/options/folder_open.png style=\"vertical-align: bottom;\">&nbsp;&nbsp;";
echo "<a href=filemanager.php>File Manager</a><br><br>";

if ($NATS->Cfg->Get("site.enable.adminsql",0)==1)
	{
	echo "<img src=images/options/folder_files.png style=\"vertical-align: bottom;\">&nbsp;&nbsp;";
	echo "<a href=admin.sql.php>SQL Console</a><br><br>";
	}
echo "<img src=images/options/search.png style=\"vertical-align: bottom;\">&nbsp;&nbsp;";
echo "<a href=admin.dns.php>DNS Query Console</a><br><br>";
echo "<img src=images/options/save.png style=\"vertical-align: bottom;\">&nbsp;&nbsp;";
echo "<a href=admin.backup.php>Backup and Restore</a><br><br>";
echo "<img src=images/options/reply.png style=\"vertical-align: bottom;\">&nbsp;&nbsp;";
echo "<a href=admin.php?mode=status>System Status Report</a><br><br>";
echo "<img src=images/help16.png style=\"vertical-align: bottom;\">&nbsp;&nbsp;";
echo "<a href=admin.php?mode=sysinfo>System Information</a><br><br>";
}

if ($mode!="") echo "<a href=admin.php>Back to Main Admin Menu</a><br><br>";
?>


<?php
Screen_Footer();
?>
