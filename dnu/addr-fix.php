<?php

require_once("config.php");
require_once(ADMIN."config.php");
require_once(ADMIN."classes/globals.php");
require_once(ADMIN."classes/mailer.php");
require_once(ADMIN."classes/common.php");
require_once(ADMIN."classes/smtp.php");
require_once(ADMIN."classes/Snoopy.php");

$c = new Common(true, false, "addr-fix");
$c_id = $c->fetchScalarTest("select id from code_lookups where type='memberAddressTypes' and code='Company'");
$b_id = $c->fetchScalarTest("select id from code_lookups where type='memberAddressTypes' and code='Billing'");
$a = $c->fetchAllTest("select ownerid, ownertype, min(id) as min_id from addresses a where ownertype='member' and addresstype=%d group by ownerid", $b_id);
foreach($a as $k=>$v) {
	if (!$c->fetchSingleTest("select id from addresses where ownertype='member' and ownerid = %d and addresstype=%d", $v["ownerid"], $c_id)) {
		$c->execute(sprintf("update addresses set addresstype=%d where id = %d", $c_id, $v["min_id"]));
	}
}
?>