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
require_once("backend/Backend.php");
require_once("classes/Google/autoload.php");
require_once("classes/Facebook/FB.php");
require_once("classes/tcpdf/tcpdf.php");
date_default_timezone_set(TZ);
setlocale(LC_MONETARY,CURRENCY);

$c = new Common(true,false,sprintf("%s.log",date("Y-m-d")));

$src = $c->fetchSingle(sprintf("select * from order_billing where billed = 1 order by billing_date limit 1"));

$src_order = $c->fetchSingle(sprintf("select * from orders where id = %d", $src["original_id"]));
$src_service = $c->fetchSingle(sprintf("select * from product p, order_lines o where ol.order_id = %d and p.id = ol.product_id and ol.custom_package = 'S'", $src_order["id"]));
$src_packages = $c->fetchSingle(sprintf("select ol.* from order_lines o where ol.order_id = %d and ol.custom_package = 'P'", $src_order["id"]));
$src_extras = $c->fetchScalarAll(sprintf("select ol.product_id from order_lines o where ol.order_id = %d and ol.custom_package = 'A'", $src_order["id"]));

$request = array(
	"custom_weight_code"=>$src_order["custom_weight_code"],
	"custom_dimension_code"=>$src_order["custom_dimension_code"],
	"sequence"=>1,
	"prod"=>array(
		1=>array(
			"product_id"=>$src_packages["id"],
			"dimentions"=>array(
				"quantity"=>$src_packages["quantity"],
				"weight"=>1,
				"height"=>1,
				"width"=>1,
				"depth"=>1
			)
		)
	),
	"extras"=>array($src_extras),
	"custom_placed_by" => $src_order["custom_placed_by"],
	"pickupInstructions" => $src_order[""],
	"deliveryInstructions" => $src_order[""],
	"custom_declared_value" => $src_order[""],
	"custom_reference_number" => $src_order[""],
	"pickup_datetime" => $src_order[""],
	"pickup_datetime_hh" => $src_order[""],
	"pickup_datetime_mm" => $src_order[""],
	"pickup_datetime_ampm" => $src_order[""],
	"pickup_datetime_ss" => $src_order[""],
	"serviceType" => $src_order[""],
	"custom_override_price" => $src_order[""],
	"selectService" => $src_order[""],
	"t_id" => 11,
	"s_type" => ""
);

$_REQUEST = $request;
$_POST = $request;
$obj = new Custom();
$return = $obj->selectService();
$c->logMessage(__FUNCTION__,sprintf("return value is [%s]", print_r($return,true)), 1);
ob_end_flush();

?>