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

if (isset($_REQUEST['action']))
	{
	switch ($_REQUEST['action'])
		{
		case "password":
			if ($_REQUEST['p_new']!=$_REQUEST['p_confirm']) $message="Passwords Do Not Match";
			else if ($_REQUEST['p_new']=="") $message="Illegal New Password";
			else
				{
				$q="UPDATE fnuser SET password=MD5(\"".ss($_REQUEST['p_new'])."\") WHERE username=\"".ss($NATS_Session->username)."\"";
				$q.=" AND password=MD5(\"".ss($_REQUEST['p_current'])."\")";
				$NATS->DB->Query($q);
				if ($NATS->DB->Affected_Rows()>0) $message="Password Changed";
				else $message="Password Change Failed";
				}
		break;
			
		}
	}

Screen_Header("User Preferences",1,0,"","main","pref");

if (isset($_REQUEST['message'])) echo "<br><b>".$_REQUEST['message']."</b><br>";
if (isset($message)) echo "<br><b>".$message."</b><br>";

?>
<br>
<b class="subtitle">User Preferences</b><br><br>

<table border=0>
<tr><td colspan=2><b><u>Change Password</u></b></td></tr>
<form action=pref.php method=post>
<input type=hidden name=action value=password>
<tr><td align=right>Current :</td>
<td><input type=password name=p_current size=20 maxlength=60></td></tr>
<tr><td align=right>New :</td>
<td><input type=password name=p_new size=20 maxlength=60></td></tr>
<tr><td align=right>Confirm :</td>
<td><input type=password name=p_confirm size=20 maxlength=60></td></tr>
<tr><td colspan=2><input type=submit value="Change Password"></td></tr>
</form>
</table>
<br><br>


<?php
Screen_Footer();
?>
