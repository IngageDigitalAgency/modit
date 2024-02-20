<?php

session_start();
ob_start();
require_once("config.php");
require_once(ADMIN."config.php");
require_once(ADMIN."classes/globals.php");
require_once(ADMIN."classes/PHPMailer/PHPMailerAutoload.php");
require_once(ADMIN."classes/common.php");

/**
 * Class for common purge functionality
 */
class Purge extends Common {
	/**
	 * @throws Exception
	 */
	public function __construct() {
		parent::__construct(true,false,sprintf("purge-data-%s", date("Y-m-d")));
	}

    /**
     * @param $dt
     * @return void
     * @throws phpmailerException
     */
    public function doIt($dt) {
		$recurring = array_merge(array(0),$this->fetchScalarAll(sprintf("select cast(o1.authorization_transaction as integer) from orders o1 where o1.order_status & %d and cast(o1.authorization_transaction as integer) > 0 and order_date < '%s'", STATUS_RECURRING, $dt)));
		$orders = array_merge(array(0),$this->fetchScalarAll(sprintf("select id from orders where order_date < '%s' and id not in (%s) and (order_status & %d) = 0 and (order_status & %d or order_status & %d)", $dt, implode(", ", $recurring), STATUS_RECURRING, STATUS_SHIPPED, STATUS_CANCELLED)));
		$del_ids = array_merge(array(0),$this->fetchScalarAll(sprintf("select id from custom_delivery where order_id in (%s)", implode(", ", $orders))));
		$this->execute(sprintf("delete from custom_delivery_commissions where delivery_id in (%s)", implode(", ", $del_ids)));
		$this->execute(sprintf("delete from custom_delivery where order_id in (%s)", implode(", ", $orders)));
		$this->execute(sprintf("delete from addresses where ownertype='order' and ownerid in (%s)", implode(", ", $orders)));
		$this->execute(sprintf("delete from order_taxes where order_id in (%s)", implode(", ", $orders)));
		$this->execute(sprintf("delete from order_lines where order_id in (%s)", implode(", ", $orders)));
		$this->execute(sprintf("delete from order_lines_dimensions where order_id in (%s)", implode(", ", $orders)));
		$this->execute(sprintf("delete from qb_export_dtl where order_id in (%s)", implode(", ", $orders)));
		$this->execute(sprintf("delete from orders where id in (%s)", implode(", ", $orders)));
		$this->logMessage(__FUNCTION__,"*** done ***",1);
		echo "*** done ***";
	}
}
date_default_timezone_set(TZ);
setlocale(LC_MONETARY,CURRENCY);
$p = new Purge();
if ($p->checkArray("administrator:user:admin",$_SESSION) && $_SESSION["administrator"]["user"]["admin"] == 1) {
	$dt = date("Y-m-01",strtotime(sprintf("today - %d months", array_key_exists("age", $_REQUEST) && $_REQUEST["age"] > 12 ? $_REQUEST["age"] : 6)));
	echo sprintf("Purging data to %s", $dt);
	$p->doIt($dt);
}
else {
	echo "Unauthorized";
}
ob_end_flush();

?>
