<?php
ob_start();
require("include.php");
$NATS->Start();
if (!$NATS_Session->Check($NATS->DB))
	{
	header("Location: ./?login_msg=Invalid+Or+Expired+Session");
	exit();
	}
$NATS_Session->Destroy($NATS->DB);
header("Location: ./?login_msg=You+are+logged+out");
exit();
?>
