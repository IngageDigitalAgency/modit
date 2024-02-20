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
require_once("admin/custom/Quickbooks/Accounting.php");

class Processing extends common {
	function __construct() {
		if (!DEFINED('DEBUG')) 
			$debug = 1;
		else
			$debug = DEBUG > 0 ? DEBUG : 1;
		parent::__construct($debug,false,sprintf('%s',date('Y-m-d')));
	}
}

date_default_timezone_set(TZ);
setlocale(LC_MONETARY,CURRENCY);
$b = new Accounting();
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
