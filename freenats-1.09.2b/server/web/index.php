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
$session=$NATS_Session->Check($NATS->DB);
if ($session)
	{
	header("Location: main.php");
	exit();
	}
Screen_Header("Welcome to FreeNATS",3);
ob_end_flush();
?>
<br><center>
<?php
if (isset($_REQUEST['login_msg'])) echo "<b style=\"color: red; font-size: 14pt;\">".$_REQUEST['login_msg']."</b><br><br>";
else echo "<b style=\"font-size: 14pt;\">Welcome to FreeNATS</b><br><br>";

$t="<b class=\"subtitle\">Login...</b>";
	Start_Round("Please Authenticate",300);
?><center><br>
<table border=0 width=200>
<form action=login.php method=post>
<?php
if (isset($_REQUEST['url'])) echo "<input type=hidden name=\"url\" value=\"".$_REQUEST['url']."\">";
?>
<tr><td align=right>
<b>Username: </b></td><td><input type=text name=naun size=20 maxlength=32 style="width: 160px;"></td></tr>
<tr><td align=right><b>Password: </b></td><td><input type=password name=napw size=21 style="width: 160px;" maxlenth=64></td></tr>
</table><br>
<input type=submit value="Login to the FreeNATS Interface" style="font-size: 11pt;">
<!-- <br><input type=checkbox name=gotomonitor value=1> Go straight to live monitor</input> -->
</form>
</center><br>

<?php
End_Round();
?>
<br><br>
</center>
<?php
Screen_Footer();
?>
