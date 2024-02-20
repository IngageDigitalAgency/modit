<?php

require_once("config.php");
require_once(ADMIN."config.php");
require_once(ADMIN."classes/globals.php");
require_once(ADMIN."classes/mailer.php");
require_once(ADMIN."classes/common.php");
require_once(ADMIN."classes/smtp.php");
require_once(ADMIN."classes/Forms.php");
require_once(ADMIN."classes/HtmlElement.php");
require_once(ADMIN."classes/mptt.php");
require_once(ADMIN."classes/Snoopy.php");
require_once(ADMIN."classes/Google/autoload.php");
require_once(ADMIN."classes/Facebook/FB.php");
require_once(ADMIN."classes/tcpdf/tcpdf.php");
require_once(ADMIN."classes/KJV.php");
require_once(ADMIN."frontend/Frontend.php");
require_once(ADMIN."frontend/modules/custom.php");
require_once(ADMIN."frontend/modules/product.php");

/**
 * Common recurring orders functionality
 */
class Recurring extends Common {
    /**
     * @param $dt
     * @param $src_order
     * @param $src_service
     * @param $calc
     * @return false|string
     * @throws phpmailerException
     */
    function getNextDate($dt, $src_order, $src_service, $calc) {
		$this->logMessage(__FUNCTION__,sprintf("dt [%s] src_order [%s] src_service [%s]", $dt, print_r($src_order,true), print_r($src_service,true)), 1);
		switch($src_order["recurring_type"]) {
		case "Daily":
			if ($dt < date("Y-m-d")) {
				while($dt < date("Y-m-d")) {
					$dt = date("Y-m-d", strtotime(sprintf("%s + %d days", $dt, $src_order["recurring_period"])));
					$dt = $calc->calcPickup($dt,array("product_id"=>$src_service["id"]));
				}
			}
			$next_dt = date("Y-m-d", strtotime(sprintf("%s + %d days", $dt, $src_order["recurring_period"])));
			$next_dt = $calc->calcPickup($next_dt,array("product_id"=>$src_service["id"]));
			break;
		case "Weekly":
		//
		//	2 issues - schedule the next action to the next date open, but subsequent back to the original day of the wek
		//	i.e. - closed friday, pickup monday, but the next is the following friday
		//
/*
			if ($dt < date("Y-m-d")) {
				while($dt < date("Y-m-d")) {
					$dt = date("Y-m-d", strtotime(sprintf("%s + %d weeks", $dt, $src_order["recurring_period"])));
					$dt = $calc->calcPickup($dt,array("product_id"=>$src_service["id"]));
				}
			}
			$next_dt = date("Y-m-d", strtotime(sprintf("%s + %d weeks", $dt, $src_order["recurring_period"])));
			$next_dt = $calc->calcPickup($next_dt,array("product_id"=>$src_service["id"]));
*/
			$next_dt = date("Y-m-d", strtotime(sprintf("%s + %d weeks", $dt, $src_order["recurring_period"])));
			break;
		case "Monthly":
			break;
		}
		return $next_dt;
	}

    /**
     * @return void
     * @throws SoapFault
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws phpmailerException
     */
    function processRecurring() {
		$c = new Common(true,false,sprintf("%s",date("Y-m-d")));
		$c->m_module = array();
		$c->config = new KJV();
		if ($c->getDebug() < 2) $c->setDebug(2);
		$c->execute(sprintf("update drivers set disabled_as_of='0000-00-00 00:00:00'"));
		$su = $c->fetchSingle(sprintf("select * from members where custom_super_user = 1 and enabled = 1 and deleted = 0"));
		$c->logMeIn($su["username"], $su["password"], 0, $su["id"]);
		$_SESSION["mgmt"]["user"] = $_SESSION["user"]["info"];	// set up super user 
		unset($_SESSION["cart"]);

		if (!($log = $c->fetchSingle(sprintf("select * from order_processing where bill_date = '%s'", date("Y-m-d"))))) {
			echo sprintf("Closed for the day %s\r\n", date("d-M-Y"));
			exit;
		}
		else $log_id = $log["id"];
		$all_to_bill = $c->fetchAll(sprintf('select b.*, o.authorization_transaction 
		from order_billing b, orders o, order_lines ol, product p
		where b.billed = 0 and o.id = b.original_id and o.order_status & %1$d = %1$d and billing_date <= CURRENT_DATE() 
		and ol.order_id = o.id and ol.custom_package = "S" and p.id = ol.product_id and p.custom_availability & %2$d
		group by original_id order by billing_date', STATUS_PROCESSING | STATUS_RECURRING, 2**date("w")));
		$c->execute(sprintf("update order_processing set started='%s' where id = %d", date(DATE_ATOM), $log_id));
		foreach($all_to_bill as $k=>$src) {
			$src_order = $c->fetchSingle(sprintf("select * from orders where id = %d", $src["original_id"]));
			$user = $c->fetchSingle(sprintf("select * from members where id = %d", $src_order["member_id"]));
			if (array_key_exists("cart",$_SESSION)) unset($_SESSION["cart"]);
			$c->logMeIn($user["username"], $user["password"]);
		$c->logMessage(__FUNCTION__, sprintf("*** post logmein session [%s]", print_r($_SESSION,true)), 1);
			$src_service = $c->fetchSingle(sprintf("select p.*, ol.line_id from product p, order_lines ol where ol.order_id = %d and p.id = ol.product_id and ol.custom_package = 'S' and ol.deleted = 0", $src_order["id"]));
			$src_packages = $c->fetchAll(sprintf("select ol.* from order_lines ol where ol.order_id = %d and ol.custom_package = 'P' and ol.deleted = 0", $src_order["id"]));
			$src_contracted = $c->fetchSingle(sprintf("select * from order_lines where order_id = %d and product_id = %d and deleted = 0", $src_order["id"], CONTRACTED_RATE));
			$c->logMessage(__FUNCTION__, sprintf("src_packages [%s]", print_r($src_packages,true)), 1);
			$src_extras = $c->fetchScalarAll(sprintf("select ol.product_id from order_lines ol, product p where ol.order_id = %d and ol.custom_package = 'A' and p.id = ol.product_id and p.custom_special_requirement and ol.deleted = 0", $src_order["id"]));
			$src_dimensions = array();
			$src_product = array();
			$qty = 0;
			$wt = 0;
			foreach($src_packages as $k=>$v) {
				$src_product[$k]["product_id"] = $v["product_id"];
				$src_product[$k]["dimensions"] = $c->fetchAll(sprintf("select quantity,weight,height,width,depth from order_lines_dimensions od where od.order_id = %d and od.line_id = %d", $src_order["id"], $v["line_id"]));
				foreach($src_product[$k]["dimensions"] as $sk=>$sv) {
					$wt += $sv["weight"];
					$qty = $sv["quantity"];
				}
				$src_product[$k]["custom_weight"] = $wt;
				$src_product[$k]["quantity"] = $qty;
			}
			$pickup = $c->fetchSingle(sprintf("select * from addresses where ownertype='order' and ownerid=%d and addresstype=%d", $src_order["id"], ADDRESS_PICKUP));
			$delivery = $c->fetchSingle(sprintf("select * from addresses where ownertype='order' and ownerid=%d and addresstype=%d", $src_order["id"], ADDRESS_DELIVERY));
			if ($src_order["custom_recurring_pu_time"] !="00:00:00") {
				$c->logMessage(__FUNCTION__,sprintf("supercede pickup time to [%s]",$src_order["custom_recurring_pu_time"]),1);
				$_SESSION["cart"]["header"]["pickup_datetime"] = date(DATE_ATOM, strtotime($src_order["custom_recurring_pu_time"]));
			}
			else {
				$dt = $c->fetchScalar(sprintf("select scheduled_date from custom_delivery where order_id = %d and service_type='P'", $src["authorization_transaction"]));
				$_SESSION["cart"]["header"]["pickup_datetime"] = date(DATE_ATOM, strtotime(sprintf("%s",explode(" ",$dt)[1])));
			}
			$_SESSION["quote"]["custom_declared_value"] = $src_order["custom_declared_value"];
			$_SESSION["cart"]["header"]["custom_declared_value"] = $src_order["custom_declared_value"];
		
		//
		//	check for a contracted rate - supercedes all other pricing
		//
			if (is_array($src_contracted)) {
				$c->logMessage(__FUNCTION__,sprintf("set override price to [%s]", $src_contracted["price"]),1);
				$_SESSION["cart"]["header"]["custom_override_price"] = $src_contracted["price"];
			} else {
				$_SESSION["cart"]["header"]["custom_override_price"] = 0;
			}
			$_SESSION["cart"]["addresses"]["shipping"] = $delivery;
			$_SESSION["cart"]["addresses"]["pickup"] = $pickup;
			$group = $c->fetchSingle(sprintf("select * from members_by_folder where member_id = %d", $src_order["member_id"]));
			$g_product = $c->fetchSingle(sprintf("select * from custom_member_product_options where member_id = %d and isgroup = 1 and product_id = %d", $group["folder_id"], $src_service["id"]));
			$m_product = $c->fetchSingle(sprintf("select * from custom_member_product_options where member_id = %d and isgroup = 0 and product_id = %d", $group["id"], $src_service["id"]));
			if (!is_array($g_product)) {
				//
				//	not a valid product any longer for this customer - log and move on
				//
				$c->logMessage(__FUNCTION__,sprintf("invalid product [%d] for order [%d]", $src_service["id"], $src["original_id"] ), 1, true, true);
				$valid = false;
			}
			else {
				$product_opt = is_array($m_product) ? $m_product["id"] : $g_product["id"];
				$weight_cd = $src_order["custom_weight_code"];
				$dimension_cd = $src_order["custom_dimension_code"];
				$_SESSION["cart"]["header"]["custom_dimension_code"] = $src_order["custom_dimension_code"];
				$_SESSION["cart"]["header"]["custom_weight_code"] = $src_order["custom_weight_code"];
				$_SESSION["cart"]["header"]["custom_reference_number"] = $src_order["custom_reference_number"];
				$_SESSION["cart"]["header"]["pickupInstructions"] = $c->fetchScalar(sprintf("select instructions from custom_delivery where order_id=%d and service_type='P'",$src["authorization_transaction"]));
				$_SESSION["cart"]["header"]["deliveryInstructions"] = $c->fetchScalar(sprintf("select instructions from custom_delivery where order_id=%d and service_type='D'",$src["authorization_transaction"]));
				$_SESSION["cart"]["header"]["custom_weight_code"] = $src_order["custom_weight_code"];
				$_SESSION["cart"]["header"]["custom_dimension_code"] = $src_order["custom_dimension_code"];
				$_SESSION["cart"]["header"]["custom_insurance"] = $src_order["custom_insurance"];
				$_SESSION["cart"]["header"]["custom_declared_value"] = $src_order["custom_declared_value"];
				$_SESSION["cart"]["header"]["custom_reference_number"] = $src_order["custom_reference_number"];
				$c->logMessage(__FUNCTION__, sprintf("src_product [%s] extras [%s] pickup [%s] delivery [%s] product [%s] weight [%s] dimension [%s]", print_r($src_product,true), print_r($src_extras,true), print_r($pickup,true), print_r($delivery,true), $product_opt, $weight_cd, $dimension_cd), 1);
				$_REQUEST = array(
					"ajax" => "render",
					"t_id" => 37,
					"KJVService" => 1,
					"custom_weight_code" => $weight_cd,
					"custom_dimension_code" => $dimension_cd,
					"serviceType" => $product_opt
				);
				$_POST = $_REQUEST;
			//
			//	Fedex check - check fas of p/u & delivery
			//
				$allowedZones = $c->fetchScalarAll(sprintf("select zone_id from zones_by_folder z, members m where m.id = %d and z.folder_id = m.custom_zones", $c->getUserInfo("id")));
			
				$fromZone = $c->fetchSingle(sprintf("select zf.*, f.downtown from zone_fsa zf, fsa f where f.fsa='%s' and zf.fsa_id = f.id and zone_id in (%s)",
									strtoupper(substr($_SESSION["cart"]["addresses"]["pickup"]["postalcode"],0,3)), is_array($allowedZones) ? implode(", ", $allowedZones) : 0));
				$toZone = $c->fetchSingle(sprintf("select zf.*, f.downtown from zone_fsa zf, fsa f where f.fsa='%s' and zf.fsa_id = f.id and zone_id in (%s)",
									strtoupper(substr($_SESSION["cart"]["addresses"]["shipping"]["postalcode"],0,3)), is_array($allowedZones) ? implode(", ", $allowedZones) : 0));
				//
				//	inzone = kjv delivery or fedex [different rating code]
				//
				$_SESSION["cart"]["header"]["inzone"] = (is_array($fromZone) && is_array($toZone)) ? 1:0;
			
				$calc = new Custom(0);
				$cart = $_SESSION["cart"];

				if ($src_service["is_fedex"]==1) {
					$quote["wt"] = $c->fetchSingle(sprintf("select * from code_lookups where id = %d",$weight_cd));
					$quote["sz"] = $c->fetchSingle(sprintf("select * from code_lookups where id = %d",$dimension_cd));
					$quote["custom_declared_value"] = $cart["header"]["custom_declared_value"];
					$quote["prod"] = array();
					foreach($src_product as $k=>$v) {
						$quote["prod"][] = $v;
					}
					$_SESSION["quote"]["prod"] = $quote["prod"];
					$cart = $calc->getFedex($cart,$quote);
					$_SESSION["cart"] = $cart;
					$c->logMessage(__FUNCTION__, sprintf("cart after getfedex [%s]", print_r($cart,true)),1);
					if (!(count($cart) > 0 && $c->checkArray("custom:rates",$cart))) {
						$c->addEcomError(sprintf("No valid FedEx rate returned"));
						$c->execute(sprintf("update order_processing set errors = errors+1 where id = %d", $log_id));
						$c->execute(sprintf("insert into order_processing_details(processing_id, order_id, processing_status, comments) values(%d, %d, 2, '%s')", $log_id, $src_order["id"], $GLOBALS['globals']->showEcomMessages().$GLOBALS['globals']->showEcomErrors()));
						continue;
					}
				}
			
				$quote = $c->config->getPrice( $src_product, $src_extras, $pickup, $delivery, $product_opt, $weight_cd, $dimension_cd );
				$quote["pickupInstructions"] = $c->fetchScalar(sprintf("select instructions from custom_delivery where order_id = %d and service_type='P'",$src["authorization_transaction"]));
				$quote["deliveryInstructions"] = $c->fetchScalar(sprintf("select instructions from custom_delivery where order_id = %d and service_type='P'",$src["authorization_transaction"]));
				$_SESSION["quote"] = $quote;
				$p_obj = new product(0);
				$c->beginTransaction();
				$c->logMessage(__FUNCTION__,sprintf("^^^ cart before createOrder [%s]", print_r($_SESSION["cart"],true)),1);
				$valid = $p_obj->createOrder($_SESSION["cart"],$orderid);
				if ($valid) {
	//
	//	auto-assign if the recurring drivers are set
	//
	if ($src_order["custom_recurring_pu_driver"] != 0) {
		$quote["custom_recurring_pu_driver"] = $src_order["custom_recurring_pu_driver"] > 0 ? $this->fetchScalar(sprintf("select d.id from drivers d, members m where m.id = d.member_id and m.deleted = 0 and m.enabled = 1 and d.deleted = 0 and d.enabled = 1 and d.id = %d",$src_order["custom_recurring_pu_driver"])) : 0;
	}
	if ($src_order["custom_recurring_del_driver"] != 0) {
		$quote["custom_recurring_del_driver"] = $src_order["custom_recurring_del_driver"] > 0 ? $this->fetchScalar(sprintf("select d.id from drivers d, members m where m.id = d.member_id and m.deleted = 0 and m.enabled = 1 and d.deleted = 0 and d.enabled = 1 and d.id = %d",$src_order["custom_recurring_del_driver"])) : 0;
	}
	$quote["fromNightly"] = 1;
	$_SESSION["quote"] = $quote;
	$c->logMessage(__FUNCTION__,sprintf("pre-postSaleProcessing session is [%s] src_order [%s]", print_r($_SESSION,true), print_r($src_order,true)),1);
					$calc->postSaleProcessing($orderid,$valid,new Common(false));
					$c->execute(sprintf("update orders set order_status = %d where id = %d", STATUS_PROCESSING, $orderid));
					$c->execute(sprintf("update order_billing set order_id = %d, billed = 1, billed_on = '%s' where id = %d", $orderid, date(DATE_ATOM), $src["id"]));
					$c->execute(sprintf("update order_processing set billed = billed+1 where id = %d", $log_id));
					//
					//	why was this commented to always execute
					//
					$reschedule = $c->fetchAll(sprintf("select * from order_billing where original_id = %d and billed = 0 order by period_number", $src["original_id"]));
					$dt = $src["billing_date"];
					$period = 0;
					if ($src_order["recurring_type"] == "Daily" && date("Y-m-d",strtotime($src["billing_date"])) < date("Y-m-d")) {
						//
						//	reschedule subsequent orders
						//
						//
						//	Assume the original date is correct and reschedule based on it
						//	If it was scheduled for a Monday, recurring weekly, make sure it recurs on the next Monday
						//
						foreach($reschedule as $k=>$v) {
							$dt = $this->getNextDate($dt,$src_order,$src_service,$calc);
							$c->execute(sprintf("update order_billing set billing_date = '%s' where id = %d", $dt, $v["id"]));
							$period = $v["period_number"];
						}
					}
					if (count($reschedule) == 0) {
						$dt = date("Y-m-d");
					}
					$period = $c->fetchSingle(sprintf("select * from order_billing where original_id = %d order by period_number desc", $src["original_id"]));
					$next_dt = $this->getNextDate($period["billing_date"],$src_order,$src_service,$calc);
					$c->execute(sprintf("insert into order_billing( original_id, billing_date, period_number ) values(%d, '%s', %d)", $src_order["id"], $next_dt, $period["period_number"]+1));
				}
			}
			if ($valid) {
				$c->commitTransaction();
				echo "Created ".$orderid.PHP_EOL;
				//$c->execute(sprintf("insert into order_processing_details(processing_id, order_id, processing_status, comments) values(%d, %d, 0, '%s')", $log_id, $src_order["id"], "Created"));
				$s_stmt = $this->prepare(sprintf('insert into order_processing_details(processing_id,order_id,processing_status,comments) values(?,?,?,?);'));
				$s_stmt->bindParams(array('ddds',$log_id, $src_order["id"], 0,sprintf('New Order <a href="http://%s/modit/orders/showOrder?o_id=%d" target="new">%d</a> has been placed for processing',HOSTNAME,$orderid,$orderid)));
				$s_stmt->execute();
			}
			else {
				$c->rollbackTransaction();
				echo "Failed on source order ".$src_order["id"].PHP_EOL;
				$c->execute(sprintf("update order_processing set errors = errors+1 where id = %d", $log_id));
				$c->execute(sprintf("insert into order_processing_details(processing_id, order_id, processing_status, comments) values(%d, %d, 2, '%s')", $log_id, $src_order["id"], implode(" ",$GLOBALS['globals']->getMessages())));
			}
		}
		$c->execute(sprintf("update order_processing set completed = '%s' where id = %d", date(DATE_ATOM), $log_id));
	}
}

session_start();
ob_start();
$st = explode(' ',microtime());

date_default_timezone_set(TZ);
setlocale(LC_MONETARY,CURRENCY);
set_time_limit(60*20);

$obj = new Recurring();
$obj->processRecurring();

ob_end_flush();

?>