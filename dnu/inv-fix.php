<?php

require_once("config.php");
require_once(ADMIN."config.php");
require_once(ADMIN."classes/globals.php");
require_once(ADMIN."classes/mailer.php");
require_once(ADMIN."classes/common.php");
require_once(ADMIN."classes/smtp.php");
require_once(ADMIN."classes/Snoopy.php");

$c = new Common(true, false, "inv-fix");

$o = $c->fetchAll("select o.id, o.total, o.taxes, sum(ol.taxes) as l_taxes, sum(ol.value+ol.taxes) as value, abs(o.total - sum(ol.value+ol.taxes)) as diff from orders o, order_lines ol where ol.order_id = o.id and ol.deleted = 0 group by o.id having abs(o.total - sum(ol.value+ol.taxes)) > 0 or abs(o.taxes - sum(ol.taxes)) > 0");
foreach($o as $k=>$v) {
	if ($v["diff"] < .03 || abs($v["taxes"] - $v["l_taxes"]) > 0) {
		echo sprintf("id [%d] total [%f] value [%f] diff [%f] taxes [%f] diff [%f]%s", $v["id"], $v["total"], $v["value"], $v["diff"], $v["taxes"], $v["l_taxes"], PHP_EOL);
		$c->execute(sprintf("update orders set total = %f, taxes = %f where id = %d", $v["value"], $v["l_taxes"], $v["id"]));
	}
}
?>