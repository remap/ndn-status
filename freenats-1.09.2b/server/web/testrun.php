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
if ($NATS_Session->userlevel<9) UL_Error("Test Run");



ob_end_flush();
Screen_Header("Test Run: test/".$_REQUEST['trid'],1);

if (isset($_REQUEST['message'])) echo "<b>".$_REQUEST['message']."</b><br>";
if (isset($amsg)) echo "<b>".$amsg."</b><br>";

if ( (isset($_REQUEST['action'])) && ($_REQUEST['action']=="finish") )
	{
	if (!isset($_REQUEST['confirmed']))
		{
		echo "<b>Manually Close Test Session</b><br>";
		echo "Are you sure you want to do this? Only close sessions that you're sure aren't still running in the background.<br>";
		echo "This <b>does not</b> kill processes - just marks the test session as complete.<br><br>";
		echo "<b>Confirm Action:</b> <a href=testrun.php?trid=".$_REQUEST['trid']."&action=finish&confirmed=1>Yes - Delete</a> | <a href=main.php>No - Cancel</a>";
		echo "<br><br>";
		}
	else
		{
		$q="UPDATE fntestrun SET finishx=".time()." WHERE trid=".ss($_REQUEST['trid']);
		$NATS->DB->Query($q);
		echo "<b>Session Closed</b><br><Br>";
		}
	}

echo "<br><b class=\"minortitle\">Test Run test/".$_REQUEST['trid']."</b><br><br>";

$q="SELECT * FROM fntestrun WHERE trid=".ss($_REQUEST['trid'])." LIMIT 0,1";
$r=$NATS->DB->Query($q);
if (!$row=$NATS->DB->Fetch_Array($r))
	{
	echo "<b>Error fetching test run data</b><br><br>";
	Screen_Footer();
	exit();
	}
$NATS->DB->Free($r);

echo "<table border=0>";
echo "<tr><td>Started : </td>";
echo "<td>".nicedt($row['startx'])." (".dtago($row['startx']).")</td></tr>";
echo "<tr><td>Finished : </td>";
echo "<td>";
if ($row['finishx']>0) echo nicedt($row['finishx'])." (".dtago($row['finishx']).")";
else echo "Still Running (<a href=testrun.php?trid=".$_REQUEST['trid']."&action=finish>Manually Mark Finished</a>)";
echo "</td>";
echo "<tr><td>Node Filter :</td>";
echo "<td>";
if ($row['fnode']=="") echo "All Nodes";
else echo "<a href=node.php?nodeid=".$row['fnode'].">".$row['fnode']."</a>";
echo "</td></tr>";

echo "<tr><td>Sysem Log :</td>";
echo "<td><a href=log.php?f_entry=Tester+".$row['trid'].">Log Events for Tester ".$row['trid']."</a>";
echo "</td></tr>";

echo "<tr><td align=left valign=top>Output : </td>";
echo "<td align=left valign=top>";
echo $row['routput'];
echo "</td></tr>";

echo "</table>";
?>


<?php
Screen_Footer();
?>
