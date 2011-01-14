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

if ($NATS_Session->Create($NATS->DB,$_REQUEST['naun'],$_REQUEST['napw']))
	{
	$loc="main.php";
	if ($NATS->Cfg->Get("site.login.nocheck",0)!="1")
		$loc.="?check_updates=1&quiet_check=1";
	if (isset($_REQUEST['url'])) $loc=$_REQUEST['url'];
	
	if ($NATS->Cfg->Get("freenats.firstrun")=="1") $loc="welcome.php";
	
	header("Location: ".$loc);
	exit();
	}
	
header("Location: ./?login_msg=Login+Failed");
exit();
?>