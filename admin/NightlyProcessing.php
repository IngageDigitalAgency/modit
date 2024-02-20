<?php

session_start();
ob_start();
$st = explode(' ',microtime());

require_once("config.php");
require_once("classes/globals.php");
require_once("classes/mailer.php");
require_once("classes/common.php");
require_once("classes/smtp.php");
require_once("classes/Forms.php");
require_once("classes/HtmlElement.php");
require_once("classes/mptt.php");
require_once("classes/Snoopy.php");
require_once("classes/Analytics.php");
require_once("classes/tcpdf/tcpdf.php");
require_once("classes/Batch.php");

require_once("frontend/Frontend.php");
require_once("frontend/modules/custom.php");
require_once("frontend/modules/product.php");

/**
 * Common processing functionality
 */
class Processing extends common {
	/**
	 * @throws Exception
	 */
	function __construct() {
		if (!DEFINED('DEBUG')) 
			$debug = 1;
		else
			$debug = DEBUG > 0 ? DEBUG : 1;
		parent::__construct($debug,false,sprintf('%s',date('Y-m-d')));
	}

    /**
     * @return void
     * @throws phpmailerException
     */
    function setCommissions() {
		$ids = $this->fetchScalarAll("select distinct(order_id) from custom_delivery where service_type='P' and completed = 1 and percent_of_delivery = 0 and order_id in (select order_id from custom_delivery where service_type='D' and completed = 1 and percent_of_delivery = 0)");
		$this->logMessage(__FUNCTION__,sprintf("orders to be allocated [%s]",implode(",",$ids)),1);
		foreach($ids as $key=>$orderId) {
			$pickup = $this->fetchSingle(sprintf("select c.*, d.commission, v.fuel_charge, o.custom_commissionable_amt from custom_delivery c, drivers d, vehicles v, orders o where c.order_id = %d and c.service_type='P' and d.id = c.driver_id and v.id = d.vehicle_id and o.id = c.order_id",$orderId));
			$delivery = $this->fetchSingle(sprintf("select c.*, d.commission, v.fuel_charge from custom_delivery c, drivers d, vehicles v where c.order_id = %d and c.service_type='D' and d.id = c.driver_id and v.id = d.vehicle_id",$orderId));
			$product = $this->fetchSingle(sprintf("select * from product where id = %d",$pickup["delivery_type"]));
			//
			//	car -> car delivery [fuel_charge != 0]
			//
			if ($pickup["fuel_charge"] != 0 && $delivery["fuel_charge"] != 0) {
				$this->logMessage(__FUNCTION__,sprintf("pickup [%s] delivery [%s]",print_r($pickup,true),print_r($delivery,true)),1);
				$d = $product["custom_driver_split"];
				$p = 100 - $product["custom_driver_split"];
				$this->execute(sprintf("update custom_delivery set percent_of_delivery = %d where id = %d", $d, $delivery["id"]));
				$this->execute(sprintf("update custom_delivery set percent_of_delivery = %d where id = %d", $p, $pickup["id"]));
			}
			$c = new custom(0);
			$c->calcDriverCommission($delivery["id"]);
			$c->calcDriverCommission($pickup["id"]);
		}
	}
}

date_default_timezone_set(TZ);
setlocale(LC_MONETARY,CURRENCY);
//$p = new Processing();
//$p->setCommissions();

$b = new Batch();
$b->setOptions(array('userEmail'=>1,'receiptPrint'=>163,'usePassed'=>1,'adminLog'=>161));
if ($rec = $b->fetchSingle(sprintf('select * from order_processing where bill_date <= curdate() and started = "0000-00-00 00:00:00" order by bill_date limit 1'))) {
	$b->setProcessing($rec);
	$b->execute(sprintf('update order_processing set started = now() where id = %d',$rec['id']));
	//
	//	process orders that should be billed
	//
	$b->processOrders();
	//
	//	check orders that were not billed
	//
	$b->checkOrders();
	$b->sendProcessingReport();
	$b->execute(sprintf('update order_processing set completed = now(), billed = %d, errors = %d, warnings = %d where id = %d',$b->getProcessed(),$b->getErrors(),$b->getWarnings(),$rec['id']));
}
$et = explode(' ',microtime());
echo sprintf('<!-- render runtime is %f seconds -->',$et[1] - $st[1] + $et[0] - $st[0]).PHP_EOL;
$b->logMessage(__FUNCTION__,sprintf('completed in %f seconds',$et[1] - $st[1] + $et[0] - $st[0]),1);
ob_end_flush();

?>
