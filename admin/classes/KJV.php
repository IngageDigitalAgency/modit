<?php

function cRateSort( $r1, $r2 ) {
	if ($r1['net'] == $r2['net'])
		return 0;
	return ($r1['net'] < $r2['net']) ? -1 : 1;
}

class KJV extends Common {

	public function getPrice( $products, $extras, $pickup, $shipto, $c_id, $weight_code, $dimension_code ) {
		$this->logMessage(__FUNCTION__,sprintf("products [%s] extras[%s] pickup [%s] shipto [%s] product [%s] weight[%s] dimension [%s]",
			print_r($products,true), print_r($extras,true), print_r($pickup,true), print_r($shipto,true), $c_id, $weight_code, $dimension_code), 2);
		if (array_key_exists(0,$extras) && $extras[0] == 0) {
			unset($extras[0]);
		}
		$weightToShip = 0;
		foreach($products as $key=>$p) {
			$weightToShip += $p["custom_weight"];
		}
		$weightConversion = $this->fetchScalar(sprintf("select extra from code_lookups where id = %d",$weight_code));
		$dimensionConversion = $this->fetchScalar(sprintf("select extra from code_lookups where id = %d",$dimension_code));
		$weightToShip = round($weightToShip*$weightConversion,1);
		$cart = Ecom::getCart();
		$cart["header"]["order_id"] = 0;
		$quote = array("custom_declared_value"=>$_SESSION["quote"]["custom_declared_value"]);
		if ($this->checkArray("quote", $_SESSION)) $quote = $_SESSION["quote"];
		$group = $this->fetchSingle(sprintf("select f.* from members_folders f, members_by_folder m where m.member_id = %d and f.id = m.folder_id limit 1",$this->getUserInfo("id", true)));
		$member = $this->fetchSingle(sprintf("select * from members where id = %d",$this->getUserInfo("id", true)));
		$product = $this->fetchSingle(sprintf("select p.* from product p, custom_member_product_options c where c.id = %d and p.id = c.product_id",$c_id));
		$p_id = $product["id"];
		$g_override = $this->fetchSingle(sprintf("select * from custom_member_product_options where member_id = %d and isgroup = 1 and product_id = %d",$group["id"],$product["id"]));
		if (!is_array($g_override))
			$g_override = array("id"=>0,"inter_downtown"=>0,"minimum_charge"=>0,"out_of_zone_rate"=>0,"km_charge"=>0,"km_mincharge"=>0,"km_maxcharge"=>0,"zone_surcharge"=>0,"fuel_exempt"=>0);
		$m_override = $this->fetchSingle(sprintf("select * from custom_member_product_options c, members_by_folder mf where mf.member_id = %d and mf.folder_id = %d and c.member_id = mf.id and c.isgroup = 0 and c.product_id = %d",$member["id"],$group["id"],$product["id"]));

		//
		//	individual products can be toggled between by km or by zone
		//
		$xxmode = $this->byKmOverride($product);
		$this->logMessage(__FUNCTION__, sprintf("byKmOrZone [%s] for product [%s]", $xxmode, print_r($product["name"], true)), 1);

		//
		//	zones are now allowed in multiple groups, but should only be in 1 group available to this customer
		//
		$allowedZones = $this->fetchScalarAll(sprintf("select zone_id from zones_by_folder z where folder_id = %d", $member["custom_zones"]));
		
		$fromZone = $this->fetchSingle(sprintf("select zf.*, f.downtown from zone_fsa zf, fsa f  where f.fsa='%s' and zf.fsa_id = f.id and zf.zone_id in (%s) and zf.enabled = 1",strtoupper(substr($pickup["postalcode"],0,3)), is_array($allowedZones) && count($allowedZones) > 0 ? implode(", ",$allowedZones) : 0));
		$toZone = $this->fetchSingle(sprintf("select zf.*, f.downtown from zone_fsa zf, fsa f where f.fsa='%s' and zf.fsa_id = f.id and zf.zone_id in (%s) and zf.enabled = 1",strtoupper(substr($shipto["postalcode"],0,3)), is_array($allowedZones) && count($allowedZones) > 0 ? implode(", ",$allowedZones) : 0 ));

		$override = 0;
		$contractedRate = 0;

		//
		//	Extension to the CONTRACTED_RATE & Fuel Exempt - check for a corresponding entry in custom_product_by_pc based on from/to postal codes
		//
		if ($this->checkArray("header:custom_override_price",$cart) && $cart["header"]["custom_override_price"] >= .01) $contractedRate = $cart["header"]["custom_override_price"];
		$from_pc = strtoupper(str_replace(" ","",$pickup["postalcode"]));
		$to_pc = strtoupper(str_replace(" ","",$shipto["postalcode"]));
		if ($by_pc = $this->fetchSingle(sprintf('select * from custom_product_by_pc where member_product_option_id = %d and ((from_postal_code = "%s" and to_postal_code = "%s") or (from_postal_code = "%3$s" and to_postal_code = "%2$s"))', $c_id, $from_pc, $to_pc))) {
			$this->logMessage(__FUNCTION__, sprintf("start new member contracted rate logic"),1);
			$contractedRate = $by_pc["contracted_rate"];
		}
		else {
			if (is_array($g_override)) {
				if ($by_pc = $this->fetchSingle(sprintf('select * from custom_product_by_pc where member_product_option_id = %d and ((from_postal_code = "%s" and to_postal_code = "%s") or (from_postal_code = "%3$s" and to_postal_code = "%2$s"))', $g_override["id"], $from_pc, $to_pc))) {
					$this->logMessage(__FUNCTION__, sprintf("start new group contracted rate logic"),1);
					$contractedRate = $by_pc["contracted_rate"];
				}
			}
		}

		if (is_array($fromZone) && is_array($toZone) && $fromZone["downtown"] && $toZone["downtown"]) {
			$this->logMessage(__FUNCTION__, sprintf("overriding to inter downtown rate from [%s] to [%s]", print_r($fromZone,true), print_r($toZone,true)), 1);
			$price = $product["custom_inter_downtown"];
			if (is_array($g_override)) {
				$override = $g_override["inter_downtown"];
			}
			if (is_array($m_override)) {
				$override += $m_override["inter_downtown"];
			}
		}
		else {
			$price = $product["custom_minimum_charge"];
			if (is_array($g_override)) {
				$override = $g_override["minimum_charge"];
			}
			if (is_array($m_override)) {
				$override += $m_override["minimum_charge"];
			}
		}
		$price = round($price * (1+$override/100),2);
		$this->logMessage(__FUNCTION__,sprintf("price is now %.2f from override %.2f", $price, $override),3);
		$calcDt = true;
		if ($product["is_fedex"]) {
			foreach($cart["custom"]["ourRates"] as $k=>$r) {
				if ($r["product_id"] == $product["id"] && array_key_exists("expectedDelivery",$r) && $r["expectedDelivery"] != "") {
					$calcDt = false;
					$quote["scheduled_delivery"] = $r["expectedDelivery"];
				}
			}
		}
		if ($calcDt  && $this->checkArray("header:pickup_datetime",$cart)) $quote["scheduled_delivery"] = $this->calcDelivery($cart["header"]["pickup_datetime"],$product);
		$quote["fuelCharge"] = 0;
		$total = 0;
		$price = round($price,2);

		$specials = $this->fetchScalarAll(sprintf("select id from product where deleted = 0 and enabled = 1 and published = 1 and custom_special_requirement = 1 and id in (%s)", implode(", ",array_merge(array(0),array_values($extras)))));
		if ((!$product["custom_same_day"]) && count($specials)==0) {
			$pickupDriver = 0;	//$this->fetchSingle(sprintf("select d.*, v.name as vehicleType from fsa f, delivery_zones_fsa dzf, delivery_zones dz, drivers d, vehicles v where f.fsa = '%s' and dzf.fsa_id = f.id and dz.id = dzf.delivery_zone_id and d.id = dz.driver_id and v.id = d.vehicle_id and v.max_weight >= %s and (disabled_as_of = '0000-00-00 00:00:00' or date(disabled_as_of) < current_date()) order by max_weight limit 1",substr($pickup["postalcode"],0,3),$weightToShip));
			$deliveryDriver = $this->fetchSingle(sprintf("select d.*, v.name as vehicleType from fsa f, delivery_zones_fsa dzf, delivery_zones dz, drivers d, vehicles v where f.fsa = '%s' and dzf.fsa_id = f.id and dz.id = dzf.delivery_zone_id and d.id = dz.driver_id and v.id = d.vehicle_id and v.max_weight >= %s order by max_weight limit 1",substr($shipto["postalcode"],0,3),$weightToShip));
			$quote["pickup_driver"] = is_array($pickupDriver) ? $pickupDriver : array("id"=>0);
			$quote["delivery_driver"] = is_array($deliveryDriver) ? $deliveryDriver : array("id"=>0);
		}
		else {
			$pickupDriver = array("id"=>0);
			$deliveryDriver = array("id"=>0);
		}
		//
		//	check for drivers assigned at order placement time
		//
		if (array_key_exists("custom_recurring_pu_driver",$_REQUEST) && $_REQUEST["custom_recurring_pu_driver"] > 0) {
			$quote["pickup_driver"] = $this->fetchSingle(sprintf("select * from drivers where id = %d",$_REQUEST["custom_recurring_pu_driver"]));
			$quote["custom_recurring_pu_driver"] = $_REQUEST["custom_recurring_pu_driver"];
		}
		if ($this->checkArray("quote:custom_recurring_pu_driver",$_SESSION) && $_SESSION["quote"]["custom_recurring_pu_driver"] > 0) {
			$quote["pickup_driver"] = $this->fetchSingle(sprintf("select * from drivers where id = %d",$_SESSION["quote"]["custom_recurring_pu_driver"]));
			$quote["custom_recurring_pu_driver"] = $_SESSION["quote"]["custom_recurring_pu_driver"];
		}
		if (array_key_exists("custom_recurring_del_driver",$_REQUEST) && $_REQUEST["custom_recurring_del_driver"] > 0) {
			$quote["delivery_driver"] = $this->fetchSingle(sprintf("select * from drivers where id = %d",$_REQUEST["custom_recurring_del_driver"]));
			$quote["custom_recurring_del_driver"] = $_REQUEST["custom_recurring_del_driver"];
		}
		if ($this->checkArray("quote:custom_recurring_del_driver",$_SESSION) && $_SESSION["quote"]["custom_recurring_del_driver"] > 0) {
			$quote["delivery_driver"] = $this->fetchSingle(sprintf("select * from drivers where id = %d",$_SESSION["quote"]["custom_recurring_del_driver"]));
			$quote["custom_recurring_del_driver"] = $_SESSION["quote"]["custom_recurring_del_driver"];
		}
		//
		//	Insurance check
		//
		$quote["insuranceCharge"] = 0;
		if ($quote["custom_declared_value"] > 0) {
			$tmp = $this->fetchSingle(sprintf("select * from product where id = %d",INSURANCE));
			$p = round($tmp["custom_minimum_charge"] + $group["custom_insurance_override"] + $member["custom_insurance_override"],2);

			$insuranceCharge = $p*$quote["custom_declared_value"]/100;
			$quote["insuranceCharge"] = round($insuranceCharge,2);
		}

		$weight_pricing = $this->fetchAll(sprintf("select * from product_pricing where product_id = %d order by min_quantity",$group["custom_weight"]));
		if ($member["custom_free_weight"] > 0)
			$weightToShip -= $member["custom_free_weight"];
		elseif ($group["custom_free_weight"] > 0)
			$weightToShip -= $group["custom_free_weight"];
		if ($weightToShip < 0) $weightToShip = 0;
		$this->logMessage(__FUNCTION__,sprintf("weight after free deducted is %.2f",$weightToShip),3);
		$quote["price"] = $price;
		$quote["weight"] = $weightToShip;
		$quote["fuelRate"] = 0;
		$weightCharge = 0;
		foreach($weight_pricing as $key=>$value) {
			if ($weightToShip > 0) {
				$weightCharge += (min($weightToShip,$value["max_quantity"] - $value["min_quantity"]) * $value["price"]);
				$weightToShip -= ($value["max_quantity"] - $value["min_quantity"]);
				$this->logMessage(__FUNCTION__,sprintf("post weightCharge %.2f weightToShip %.2f min [%d] max [%d]",$weightCharge,$weightToShip,$value["min_quantity"],$value["max_quantity"]),3);
			}
		}
		$weightCharge = $weightCharge * (1+($group["custom_weight_override"]+$member["custom_weight_override"])/100);
		$quote["weightCharge"] = round($weightCharge,2);

		$allowedZones = $this->fetchScalarAll(sprintf("select zone_id from zones_by_folder z, members m where m.id = %d and z.folder_id = m.custom_zones", $this->getUserInfo("id", true)));

		$fromZone = $this->fetchSingle(sprintf("select zf.*, f.downtown from zone_fsa zf, fsa f where f.fsa='%s' and zf.fsa_id = f.id and zone_id in (%s)",
			strtoupper(substr($pickup["postalcode"],0,3)), count($allowedZones) > 0 ? implode(", ", $allowedZones) : 0));
		$toZone = $this->fetchSingle(sprintf("select zf.*, f.downtown from zone_fsa zf, fsa f where f.fsa='%s' and zf.fsa_id = f.id and zone_id in (%s)",
			strtoupper(substr($shipto["postalcode"],0,3)), count($allowedZones) > 0 ? implode(", ", $allowedZones) : 0));
		$outOfZone = 0;
		if (!(is_array($fromZone) && is_array($toZone))) {
			//
			//	Is this a fedex trip or a kjv same day
			//
			if (!$product["custom_same_day"]) {
				//
				//	out of region delivery - get a fedex rate and use it.
				//
				$this->logMessage(__FUNCTION__,sprintf("let fedex handle out of zone post [%s] quote [%s] session [%s]", print_r($_POST,true), print_r($quote,true), print_r($_SESSION,true)),3);
				$quote["prod"] = $_SESSION["quote"]["prod"];
				$quote["fuelCharge"] = 0;
				//
				//	Fedex pickups are also delivered by fedex - fedex is always the delivery agent
				//
				$quote["delivery_driver"] = $this->fetchSingle(sprintf("select d.*, v.name as vehicleType from drivers d, vehicles v where d.id = %d and v.id = d.vehicle_id", FEDEX_DRIVER));
				$quote["pickup_driver"] = $quote["delivery_driver"];
				//
				//	p/u fsa is valid - kjv pickup, fedex delivery
				//
				if ($checkPickupDriver = $this->fetchSingle(sprintf("select d.*, v.name as vehicleType from fsa f, delivery_zones_fsa dzf, delivery_zones dz, drivers d, vehicles v where f.fsa = '%s' and dzf.fsa_id = f.id and dz.id = dzf.delivery_zone_id and d.id = dz.driver_id and v.id = d.vehicle_id and v.max_weight >= %s order by max_weight limit 1",substr($pickup["postalcode"],0,3),$weightToShip))) {
					$quote["pickup_driver"] = array("id"=>0);	// as per Victor - do not assign a p/u driver if in a serviced FSA
					if ($checkDeliveryDriver = $this->fetchSingle(sprintf("select d.*, v.name as vehicleType from fsa f, delivery_zones_fsa dzf, delivery_zones dz, drivers d, vehicles v where f.fsa = '%s' and dzf.fsa_id = f.id and dz.id = dzf.delivery_zone_id and d.id = dz.driver_id and v.id = d.vehicle_id and v.max_weight >= %s order by max_weight limit 1",substr($shipto["postalcode"],0,3),$weightToShip))) {
						$quote["delivery_driver"] = $checkDeliveryDriver;
					}
				}
				$quote["wt"] = $this->fetchSingle(sprintf("select * from code_lookups where id = %d",$weight_code));
				$quote["sz"] = $this->fetchSingle(sprintf("select * from code_lookups where id = %d",$dimension_code));
				$quote["custom_signature_required"] = array_key_exists("custom_signature_required",$_REQUEST) ? $_REQUEST["custom_signature_required"] : 0;
				$this->logMessage(__FUNCTION__,sprintf("calling fedex 2 cart [%s] quote [%s] session [%s] request [%s]",
						print_r($cart,true), print_r($quote,true), print_r($_SESSION,true), print_r($_REQUEST,true)),4);
				$cart = $this->getFedEx($cart,$quote);
				usort($cart['custom']['rates'],'cRateSort');
				$cart["products"] = array();
				foreach($products as $key=>$p) {
					$tmp = $this->fetchSingle(sprintf("select * from product where id = %d",$p["product_id"]));
					$key = $tmp["id"]."|0|0|0";
					$cart = $this->initPriceLine($cart,$key,$tmp);
					$cart['products'][$key]['custom_weight'] = $p["custom_weight"];
					$cart['products'][$key]['quantity'] = $p["quantity"];
					$cart['products'][$key]['custom_package'] = "P";
					$cart['products'][$key]['line_id'] = count($cart['products'])+1;
					$tmp = array();
					foreach($p["dimensions"] as $skey=>$d) {
						$tmp[$skey] = array(
								"quantity" => $d["quantity"],
								"weight" => $d["weight"],
								"height" => $d["height"],
								"width" => $d["width"],
								"depth" => $d["depth"],
								"sequence" => $skey);
					}
					$cart['products'][$key]["dimensions"] = $tmp;
				}
				foreach($cart["custom"]["ourRates"] as $key=>$value) {
					$this->logMessage(__FUNCTION__, sprintf("testing rate [%s] [%s] vs [%s]", print_r($key,true), print_r($value,true), $c_id),3);
					if ($c_id == $value["member_id"] || $c_id == $value["group_id"]) {
						$tmp = $this->fetchSingle(sprintf("select p.* from product p, custom_member_product_options cpo where cpo.id = %d and p.id = cpo.product_id",$c_id));
						$k = $tmp["id"]."|0|0|0";
						$cart = $this->initPriceLine($cart,$k,$tmp);
						$cart['products'][$k]['url'] = $this->getUrl('product',$tmp['id'],$tmp);
						$cart['products'][$k]['line_id'] = count($cart['products']);
						$cart['products'][$k]['price'] = round($value["rate"],2);
						$cart['products'][$k]['regularPrice'] = $cart['products'][$k]['price'];
						$cart['products'][$k]['value'] = $cart['products'][$k]['price'];
						$cart['products'][$k]['quantity'] = 1;
						$cart['products'][$k]['product_id'] = $product['id'];
						$cart['products'][$k]['custom_package'] = "S";
						//
						//	if minimum charge kicked in remove all discounting
						//
						if ($value["minimum_charge"]) {
							$cart['products'][$k]['custom_fedex'] = 0;
							$this->logMessage(__FUNCTION__,sprintf("minimum charge found, current data is [%s]", print_r($cart['products'][$k],true)),3);
						}
						//$quote["fuelCharge"] = $cart['products'][$k]['price'];
						foreach($value["surcharges"] as $sk=>$svalue) {
							$tmp = $this->fetchSingle(sprintf("select * from product p where id = %d",$svalue["product_id"]));
							$kk = $tmp["id"]."|0|0|0";
							$cart = $this->initPriceLine($cart,$kk,$tmp);
							$cart['products'][$kk]['url'] = $this->getUrl('product',$tmp['id'],$tmp);
							$cart['products'][$kk]['line_id'] = count($cart['products']);
							$cart['products'][$kk]['price'] = round($svalue["amount"],2);
							$cart['products'][$kk]['regularPrice'] = $cart['products'][$kk]['price'];
							$cart['products'][$kk]['value'] = $cart['products'][$kk]['price'];
							$cart['products'][$kk]['quantity'] = 1;
							$cart['products'][$kk]['product_id'] = $tmp['id'];
							$cart['products'][$kk]['custom_package'] = "A";
/*
	As per Victor (2020/12/27):
	Change is use the returned List price excluding surcharges.
	Itemize known/wanted surcharges, ignore others - he will add them manually if he sees them on a FedEx invoice

							if (!$value["minimum_charge"]) {
								$cart['products'][$k]['price'] -= round($svalue["amount"],2);
								$cart['products'][$k]['regularPrice'] = $cart['products'][$k]['price'];
								$cart['products'][$k]['value'] = $cart['products'][$k]['price'];
							}
*/
							$this->logMessage(__FUNCTION__,sprintf("after adding surcharge [%s]", print_r($cart["products"],true)),3);
						}
					}
				}

				$this->logMessage(__FUNCTION__,sprintf("cart after fedex is [%s]",print_r($cart,true)),3);

				//
				//	oda - out of district
				//	check full 6 char postal code, if it doesnt exist try the fsa only
				//
				$oda = $this->fetchSingle(sprintf("select sum(custom_minimum_charge) as charge, id from product where code in ('%s','%s') and enabled=1 and published=1 and deleted=0", str_replace(" ", "", $cart["addresses"]["pickup"]["postalcode"]), str_replace(" ", "", $cart["addresses"]["shipping"]["postalcode"])));
				if (!is_array($oda) || $oda["charge"] < 0.01) {
					$oda = $this->fetchSingle(sprintf("select sum(custom_minimum_charge) as charge, id from product where code in ('%s','%s') and enabled=1 and published=1 and deleted=0", substr(str_replace(" ", "", $cart["addresses"]["pickup"]["postalcode"]),0,3), substr(str_replace(" ", "", $cart["addresses"]["shipping"]["postalcode"]),0,3)));
				}
				if (is_array($oda) && (float)$oda["charge"] > .01) {
					$tmp = $this->fetchSingle(sprintf("select * from product where id = %d",$oda["id"]));
					$key = $tmp["id"]."|0|0|0";
					$cart = $this->initPriceLine($cart,$key,$tmp);
					$cart['products'][$key]['url'] = $this->getUrl('product',$tmp['id'],$tmp);
					$cart['products'][$key]['line_id'] = count($cart['products']);
					$cart['products'][$key]['price'] = $oda["charge"];
					$cart['products'][$key]['regularPrice'] = $oda["charge"];
					$cart['products'][$key]['value'] = $oda["charge"];
					$cart['products'][$key]['quantity'] = 1;
					$cart['products'][$key]['product_id'] = $tmp['id'];
				}

				foreach($extras as $key=>$rec) {
					if ($rec > 0) {
						$tmp = product::formatData($this->fetchSingle(sprintf("select * from product where id = %d",$rec)));
						$key = $tmp["id"]."|0|0|0";
						$cart = $this->initPriceLine($cart,$key,$tmp);
						$cart['products'][$key]['url'] = $this->getUrl('product',$tmp['id'],$tmp);
						$cart['products'][$key]['line_id'] = count($cart['products']);
						//$cart['products'][$key]['price'] = $tmp["price"];
						//$cart['products'][$key]['regularPrice'] = $tmp["price"];
						//$cart['products'][$key]['value'] = $tmp["price"];
						$cart['products'][$key]['price'] = $tmp["custom_minimum_charge"];
						$cart['products'][$key]['regularPrice'] = $tmp["custom_minimum_charge"];
						$cart['products'][$key]['value'] = $tmp["custom_minimum_charge"];
						$cart['products'][$key]['quantity'] = 1;
						$cart['products'][$key]['product_id'] = $tmp['id'];
					}
				}
				//$fuel = $this->fetchSingle(sprintf("select * from product_pricing where product_id = %d order by min_quantity limit 1",$group["custom_fuel"]));
				//$fuelCharge = round(($fuel["price"] + $group["custom_fuel_override"] + $member["custom_fuel_override"])/100,2);
				$fuel = $this->fetchSingle(sprintf("select * from product where id = %d",$group["custom_fuel"]));
				$fuelCharge = max(0,round(($fuel["custom_minimum_charge"] + $group["custom_fuel_override"] + $member["custom_fuel_override"])/100,2));

//				if ($member["custom_fuel_exempt"] == 1) {
	$this->logMessage(__FUNCTION__, sprintf("fuel exempt check member [%s] pc [%s]", print_r($m_override,true), print_r($by_pc,true)),1);
				if ($g_override["fuel_exempt"] == 1 || (is_array($m_override) && $m_override["fuel_exempt"] == 1) || (is_array($by_pc) && $by_pc["fuel_exempt"] == 1)) {
					$this->logMessage(__FUNCTION__ ,sprintf("override fuel charge to 0 from exemption (a)"),3);
					$fuelCharge = 0;
				}
				else $this->logMessage(__FUNCTION__ ,sprintf("did we get the override right from (a) member [%s] pc [%s] group [%s]", print_r($m_override,true), print_r($by_pc,true), print_r($g_override,true)),1);

				$quote["fuelRate"] = $fuelCharge;
				$quote["fuelCharge"] = 0;
				$valid = true;
				foreach($cart["products"] as $k=>$p) {
					if (!array_key_exists("custom_has_fuel_surcharge",$p)) {
						$this->logMessage(__FUNCTION__,sprintf("bad data detected key [%s] cart [%s]", $key, print_r($cart["products"],true)),1,true);
						$this->addError("Oops... something went wrong<br/>The Web Master has been notified");
						$valid = false;
						continue;
					}
					if ($p["custom_has_fuel_surcharge"]) {
						$this->logMessage(__FUNCTION__,sprintf("adding fuel surcharge for [%s]", $p["code"]),3);
						$quote["fuelCharge"] += $p['price'] * $p['quantity'];
					} else {
					 	$this->logMessage(__FUNCTION__,sprintf("skipping fuel surcharge for [%s]", $p["code"]),3);
					}
				}
				$quote["fuelCharge"] = $quote["fuelCharge"] * $fuelCharge;
				if ($quote["fuelCharge"] >= .01) {
					$tmp = $this->fetchSingle(sprintf("select * from product where id = %d",$group["custom_fuel"]));
					$key = $tmp["id"]."|0|0|0";
					$cart = $this->initPriceLine($cart,$key,$tmp);
					$cart['products'][$key]['url'] = $this->getUrl('product',$tmp['id'],$tmp);
					$cart['products'][$key]['line_id'] = count($cart['products']);
					$cart['products'][$key]['price'] = $quote["fuelCharge"];
					$cart['products'][$key]['regularPrice'] = $quote["fuelCharge"];;
					$cart['products'][$key]['value'] = $quote["fuelCharge"];
					$cart['products'][$key]['quantity'] = 1;
					$cart['products'][$key]['product_id'] = $tmp['id'];
					$cart['products'][$key]['qty_multiplier'] = 1;
					//$cart['products'][$key]['recurring_discount_rate'] = $fuelCharge;
				}
				foreach($cart["products"] as $key=>$item) {
					$cart["products"][$key] = Ecom::lineValue($item);
				}


				if ($valid) {
					$cart = Ecom::recalcOrder($cart);
					$cart = $this->resortProducts($cart);
					$cart["quote"] = $quote;
					$_SESSION["cart"] = $cart;
				}
				return $quote;
			}
			else {
				$this->logMessage(__FUNCTION__,sprintf("let kjv handle out of zone header [%s]", print_r($_SESSION["cart"]["header"],true)),1);
				$_SESSION["cart"]["header"]["inzone"] = 1;
				$member["custom_by_km"] = 1;
				$outOfZone = 1;
			}
		}
		$from = $this->fetchSingle(sprintf("select bf.* from zone_folders zf, zones_by_folder bf, zones z, zone_fsa zfsa, fsa where zf.id = %d and bf.folder_id = zf.id and z.id = bf.zone_id and zfsa.zone_id = z.id and fsa.fsa = '%s' and zfsa.fsa_id = fsa.id and zfsa.enabled = 1",
				$member["custom_zones"], strtoupper(substr($pickup["postalcode"],0,3))));
		$to = $this->fetchSingle(sprintf("select bf.* from zone_folders zf, zones_by_folder bf, zones z, zone_fsa zfsa, fsa where zf.id = %d and bf.folder_id = zf.id and z.id = bf.zone_id and zfsa.zone_id = z.id and fsa.fsa = '%s' and zfsa.fsa_id = fsa.id and zfsa.enabled = 1",
				$member["custom_zones"], strtoupper(substr($shipto["postalcode"],0,3))));
		$interZone = false;
		if (is_array($from) && is_array($to))
			$interZone = $this->fetchSingle(sprintf("select * from zone_to_zone where (zone_from=%d and zone_to=%d) or (zone_from = %d and zone_to = %d)",
					$from["id"], $to["id"], $to["id"], $from["id"] ));
		$quote["zoneCharge"] = 0;
		$quote["kmCharge"] = 0;
		$quote["kmRate"] = 0;
		if ($xxmode) {	//$member["custom_by_km"] != 0) {
			$this->logMessage(__FUNCTION__,sprintf("calc by distance"),1);
			//https://maps.googleapis.com/maps/api/distancematrix/json?origins=4-5+Amelia+St,St+Thomas+ON&destinations=440+Wellington+St,St+Thomas+ON
			if ($this->checkArray("cart:header:km_calced",$_SESSION))
				$distance = $_SESSION["cart"]["header"]["km_calced"];
			else
				$distance = $this->getWalkingDistance($cart["addresses"]);
			if ($distance < 9999) {
				if ($outOfZone) {
					$kmRate = $product["custom_out_of_zone_rate"];
					$kmRate = round($kmRate * (1+($g_override["out_of_zone_rate"]+ is_array($m_override) ? $m_override["out_of_zone_rate"] : 0)/100),2);
					$quote["kmCharge"] = max($distance,1);
					$quote["kmRate"] = round($kmRate,2);
					$r["rate"] = $quote["kmRate"];
					$r["qty"] = $quote["kmCharge"];
					$r["charge"] = round($r["rate"]*$r["qty"],2);
					$kmProduct = KM_RATE;

					$rates["rate"] = 0;
					$rates["qty"] = 0;
					$rates["charge"] = 0;
					$tmpQuote = $quote;
					$kmProduct = KM_RATE;
$this->logMessage(__FUNCTION__, sprintf("///-1 tmpQuote [%s] rates [%s] kmProduct [%s] group [%s] product [%s] member [%s], distance [%s] m_override [%s] g_override [%s]",
print_r($tmpQuote,true), print_r($rates,true), print_r($kmProduct,true), print_r($group,true), print_r($product,true), print_r($member,true), print_r($distance,true), print_r($m_override,true), print_r($g_override,true)
				),1);
					$result = $this->getKmRate($tmpQuote, $rates, $kmProduct, //updated
						$group, $product, $member, $distance, $pickup, $shipto, $m_override, $g_override, false);
$this->logMessage(__FUNCTION__, sprintf("///-2 tmpQuote [%s]", print_r($tmpQuote,true)),1);
					$quote = $tmpQuote;
					$r = $rates;
				}
				else {
					$rates["rate"] = 0;
					$rates["qty"] = 0;
					$rates["charge"] = 0;
					$tmpQuote = $quote;
					$kmProduct = KM_RATE;
					$result = $this->getKmRate($tmpQuote, $rates, $kmProduct, //updated
						$group, $product, $member, $distance, $pickup, $shipto, $m_override, $g_override, true);
					$quote = $tmpQuote;
					$r = $rates;
				}
			}
			else {
				$this->logMessage(__FUNCTION__,sprintf("unable to get a route for source/destination, cart [%s]",print_r($cart,true)),1,true);
				$this->addEcomError("Unable to get a distance from the addresses supplied");
				$this->addEcomError("Ensure any unit/apt numbers are on address line 2");
				$quote["kmRate"] = 999;
				$quote["kmCharge"] = 999;
				$kmProduct = KM_MAX;
				$r["rate"] = 999;
				$r["qty"] = 1;
			}
		}
		else {
			$this->logMessage(__FUNCTION__,sprintf("calc by zone"),1);
			$zoneCost = is_array($interZone) ? $interZone["cost"] : 0;
			$quote["zonesCrossed"] = $zoneCost;
			$product["custom_zone_surcharge"] = $product["custom_zone_surcharge"] * (1+($g_override["zone_surcharge"] + (!is_array($m_override) ? 0 : $m_override["zone_surcharge"]))/100);
			$product["custom_zone_surcharge"] = round($product["custom_zone_surcharge"],2);
			$quote["zoneRate"] = $product["custom_zone_surcharge"];
			$quote["zoneCharge"] = $product["custom_zone_surcharge"] * $zoneCost;
		}

		$quote["downtownCharge"] = 0;
		
		$quote = $this->checkDowntown($this->getUserInfo("id", true),$quote, $product, $fromZone, $toZone, $pickupDriver, $deliveryDriver);

		$total = $quote["price"] + $quote["weightCharge"] + $quote["zoneCharge"] + $quote["downtownCharge"] + $quote["insuranceCharge"];
		$calc_fuel = true;
		//
		//	don't calc fuel charges if both pickup & delivery have no vehicle [bike or walking]
		//
		if (array_key_exists("pickup_driver",$quote) && array_key_exists("delivery_driver",$quote)) {
			$p_fc = $this->fetchSingle(sprintf("select v.* from vehicles v, drivers d where d.id = %d and v.id = d.vehicle_id",$quote["pickup_driver"]["id"]));
			$d_fc = $this->fetchSingle(sprintf("select v.* from vehicles v, drivers d where d.id = %d and v.id = d.vehicle_id",$quote["delivery_driver"]["id"]));
			$calc_fuel = (is_array($p_fc) && $p_fc["fuel_charge"]) || (is_array($d_fc) && $d_fc["fuel_charge"]);
		}
		if ($calc_fuel) {
			//$fuel = $this->fetchSingle(sprintf("select * from product_pricing where product_id = %d order by min_quantity limit 1",$group["custom_fuel"]));
			//$fuelCharge = $fuel["price"] + $group["custom_fuel_override"] + $member["custom_fuel_override"];
			$fuel = $this->fetchSingle(sprintf("select * from product where id = %d",$group["custom_fuel"]));
			$fuelCharge = max(0,round($fuel["custom_minimum_charge"] + $group["custom_fuel_override"] + $member["custom_fuel_override"],2));
			$quote["fuelRate"] = round($fuelCharge/100,2);

//				if ($member["custom_fuel_exempt"] == 1) {
	$this->logMessage(__FUNCTION__, sprintf("fuel exempt check mbr [%s] pc [%s] group [%s]", print_r($m_override,true), print_r($by_pc,true), print_r($g_override,true)),1);
				if ($g_override["fuel_exempt"] == 1 || (is_array($m_override) && $m_override["fuel_exempt"] == 1) || (is_array($by_pc) && $by_pc["fuel_exempt"] == 1)) {
					$this->logMessage(__FUNCTION__ ,sprintf("override fuel charge to 0 from exemption (b)"),1);
					$quote["fuelRate"] = 0;
				}
				else $this->logMessage(__FUNCTION__ ,sprintf("did we get the override right from (b) member [%s] pc [%s] group [%s]", print_r($m_override,true), print_r($by_pc,true), print_r($g_override,true)),1);

		}
		else {
			$fuelCharge = 0;
		}
		$quote["subTotal"] = $total;
		$cart["products"] = array();

		//
		//	save to the cart
		//	custom_package - 
		//		"P":	the actual package[s] to be delivered
		//		"S":	service type [9am etc]
		//		"A":	additional charges
		//
		foreach($products as $key=>$p) {
			$tmp = $this->fetchSingle(sprintf("select * from product where id = %d",$p["product_id"]));
			$key = $tmp["id"]."|0|0|0";
			$cart = $this->initPriceLine($cart,$key,$tmp);
			$cart['products'][$key]['custom_weight'] = $p["custom_weight"];
			$cart['products'][$key]['quantity'] = $p["quantity"];
			$cart['products'][$key]['custom_package'] = "P";
			$cart['products'][$key]['line_id'] = count($cart['products'])+1;
			$tmp = array();
			foreach($p["dimensions"] as $skey=>$d) {
				$tmp[$skey] = array(
						"quantity" => $d["quantity"],
						"weight" => $d["weight"],
						"height" => $d["height"],
						"width" => $d["width"],
						"depth" => $d["depth"],
						"sequence" => $skey);
			}
			$cart['products'][$key]["dimensions"] = $tmp;
		}
		//
		//	the service being provided [9am delivery etc]
		//
		$key = $product["id"]."|0|0|0";
		$cart = $this->initPriceLine($cart,$key,$product);
		$cart['products'][$key]['url'] = $this->getUrl('product',$product['id'],$product);
		$cart['products'][$key]['line_id'] = count($cart['products']);
		$cart['products'][$key]['custom_package'] = "S";
		if ($quote["kmRate"] > 0) {
			$cart['products'][$key]['price'] = 0;
			$cart['products'][$key]['regularPrice'] = 0;
			$cart['products'][$key]['value'] = 0;
		}
		else {
			$cart['products'][$key]['price'] = $quote['price'];
			$cart['products'][$key]['regularPrice'] = $cart['products'][$key]['price'];
			$cart['products'][$key]['value'] = $quote["price"];
		}
		$cart['products'][$key]['product_id'] = $product['id'];
		if ($quote["weightCharge"] != 0) {
			$tmp = $this->fetchSingle(sprintf("select * from product where id = %d",$group["custom_weight"]));
			$key = $tmp["id"]."|0|0|0";
			$cart = $this->initPriceLine($cart,$key,$tmp);
			$cart['products'][$key]['url'] = $this->getUrl('product',$tmp['id'],$tmp);
			$cart['products'][$key]['line_id'] = count($cart['products']);
			$cart['products'][$key]['price'] = round($quote["weightCharge"]/$quote["weight"],3);
			$cart['products'][$key]['regularPrice'] = $cart['products'][$key]['price'];
			$cart['products'][$key]['value'] = $quote["weightCharge"];
			$cart['products'][$key]['quantity'] = $quote["weight"];
			$cart['products'][$key]['product_id'] = $tmp['id'];
		}
		if ($quote["zoneCharge"] != 0) {
			$tmp = $this->fetchSingle(sprintf("select * from product where id = %d",ZONE_ID));
			$key = $tmp["id"]."|0|0|0";
			$cart = $this->initPriceLine($cart,$key,$tmp);
			$cart['products'][$key]['url'] = $this->getUrl('product',$tmp['id'],$tmp);
			$cart['products'][$key]['line_id'] = count($cart['products']);
			$cart['products'][$key]['price'] = $quote["zoneRate"];
			$cart['products'][$key]['regularPrice'] = $cart['products'][$key]['price'];
			$cart['products'][$key]['value'] = $quote["zoneCharge"];
			$cart['products'][$key]['quantity'] = $quote["zonesCrossed"];
			$cart['products'][$key]['product_id'] = $tmp['id'];
		}
		if ($quote["kmCharge"] != 0) {
			$tmp = $this->fetchSingle(sprintf("select * from product where id = %d",$kmProduct));
			$key = $tmp["id"]."|0|0|0";
			$cart = $this->initPriceLine($cart,$key,$tmp);
			$cart['products'][$key]['url'] = $this->getUrl('product',$tmp['id'],$tmp);
			$cart['products'][$key]['line_id'] = count($cart['products']);
			$cart['products'][$key]['price'] = $r["rate"];
			$cart['products'][$key]['regularPrice'] = $cart['products'][$key]['price'];
			$cart['products'][$key]['value'] = round($r["rate"]*$r["qty"],2);
			$cart['products'][$key]['quantity'] = $r["qty"];
			$cart['products'][$key]['product_id'] = $tmp['id'];
		}
		if ($quote["downtownCharge"] != 0) {
			$tmp = $this->fetchSingle(sprintf("select * from product where id = %d",$quote["downtownChargeType"]));
			$key = $tmp["id"]."|0|0|0";
			$cart = $this->initPriceLine($cart,$key,$tmp);
			$cart['products'][$key]['url'] = $this->getUrl('product',$tmp['id'],$tmp);
			$cart['products'][$key]['line_id'] = count($cart['products']);
			$cart['products'][$key]['price'] = $quote["downtownCharge"];
			$cart['products'][$key]['regularPrice'] = $cart['products'][$key]['price'];
			$cart['products'][$key]['value'] = $quote["downtownCharge"];
			$cart['products'][$key]['quantity'] = 1;
			$cart['products'][$key]['product_id'] = $tmp['id'];
			$cart['products'][$key]['qty_multiplier'] = 1;
		}
		if ($quote["insuranceCharge"] != 0) {
			$tmp = $this->fetchSingle(sprintf("select * from product where id = %d",INSURANCE));
			$key = $tmp["id"]."|0|0|0";
			$cart = $this->initPriceLine($cart,$key,$tmp);
			$cart['products'][$key]['url'] = $this->getUrl('product',$tmp['id'],$tmp);
			$cart['products'][$key]['line_id'] = count($cart['products']);
			$cart['products'][$key]['price'] = $quote["insuranceCharge"];
			$cart['products'][$key]['regularPrice'] = $quote["insuranceCharge"];
			$cart['products'][$key]['value'] = $quote["insuranceCharge"];
			$cart['products'][$key]['quantity'] = 1;
			$cart['products'][$key]['product_id'] = $tmp['id'];
		}

		$oda = $this->fetchSingle(sprintf("select sum(custom_minimum_charge) as charge, id from product where code in ('%s','%s') and enabled=1 and published=1 and deleted=0", str_replace(" ", "", $cart["addresses"]["pickup"]["postalcode"]), str_replace(" ", "", $cart["addresses"]["shipping"]["postalcode"])));

		//
		//	oda - out of district
		//	check full 6 char postal code, if it doesnt exist try the fsa only
		//
		$oda = $this->fetchSingle(sprintf("select sum(custom_minimum_charge) as charge, id from product where code in ('%s','%s') and enabled=1 and published=1 and deleted=0", str_replace(" ", "", $cart["addresses"]["pickup"]["postalcode"]), str_replace(" ", "", $cart["addresses"]["shipping"]["postalcode"])));
		if (!array_key_exists("charge", $oda) || $oda["charge"] < 0.01) {
			$oda = $this->fetchSingle(sprintf("select sum(custom_minimum_charge) as charge, id from product where code in ('%s','%s') and enabled=1 and published=1 and deleted=0", substr(str_replace(" ", "", $cart["addresses"]["pickup"]["postalcode"]),0,3), substr(str_replace(" ", "", $cart["addresses"]["shipping"]["postalcode"]),0,3)));
		}

		if (array_key_exists("charge",$oda) && (float)$oda["charge"] > .01) {
			$tmp = $this->fetchSingle(sprintf("select * from product where id = %d",$oda["id"]));
			$key = $tmp["id"]."|0|0|0";
			$cart = $this->initPriceLine($cart,$key,$tmp);
			$cart['products'][$key]['url'] = $this->getUrl('product',$tmp['id'],$tmp);
			$cart['products'][$key]['line_id'] = count($cart['products']);
			$cart['products'][$key]['price'] = $oda["charge"];
			$cart['products'][$key]['regularPrice'] = $oda["charge"];
			$cart['products'][$key]['value'] = $oda["charge"];
			$cart['products'][$key]['quantity'] = 1;
			$cart['products'][$key]['product_id'] = $tmp['id'];
		}

		foreach($extras as $key=>$rec) {
			if ($rec > 0) {
				$tmp = product::formatData($this->fetchSingle(sprintf("select * from product where id = %d",$rec)));
				$key = $tmp["id"]."|0|0|0";
				$cart = $this->initPriceLine($cart,$key,$tmp);
				$cart['products'][$key]['url'] = $this->getUrl('product',$tmp['id'],$tmp);
				$cart['products'][$key]['line_id'] = count($cart['products']);
				//$cart['products'][$key]['price'] = $tmp["price"];
				//$cart['products'][$key]['regularPrice'] = $tmp["price"];
				//$cart['products'][$key]['value'] = $tmp["price"];
				$cart['products'][$key]['price'] = $tmp["custom_minimum_charge"];
				$cart['products'][$key]['regularPrice'] = $tmp["custom_minimum_charge"];
				$cart['products'][$key]['value'] = $tmp["custom_minimum_charge"];
				$cart['products'][$key]['quantity'] = 1;
				$cart['products'][$key]['product_id'] = $tmp['id'];
			}
		}
		foreach($cart["products"] as $key=>$item) {
			$cart["products"][$key] = Ecom::lineValue($item);
		}

//
//
//	For recurring orders only, there is an optional price override
//	If this override exists, set service prict to 0, add the "CONTRACTED_RATE" product & pricing
//	Also, remove zone charges if they exist 
//

		if ($contractedRate >= .01) {
			foreach($cart["products"] as $k=>$v) {
				if ($v["custom_package"]=="S") {
					$cart["products"][$k]["price"] = 0;
					$cart["products"][$k]["regularPrice"] = 0;
					$cart["products"][$k]["value"] = 0;
					$cart["products"][$k] = Ecom::lineValue($cart['products'][$k]);
				}
				else {
					if (array_key_exists($v["product_id"],$GLOBALS["CONTRACTED_REMOVALS"])) {
						$this->logMessage(__FUNCTION__,sprintf("removing product [%d} as per config", $v["product_id"]),3);
						unset($cart["products"][$k]);
					}
				}
			}
			$tmp = $this->fetchSingle(sprintf("select * from product where id = %d",CONTRACTED_RATE));
			$key = $tmp["id"]."|0|0|0";
			$cart = $this->initPriceLine($cart,$key,$tmp);
			$cart["products"][$key]['url'] = $this->getUrl('product',$tmp['id'],$tmp);
			$cart["products"][$key]['line_id'] = count($cart['products']);
			$cart["products"][$key]['price'] = $contractedRate;
			$cart["products"][$key]['regularPrice'] = $contractedRate;
			$cart["products"][$key]['value'] = $contractedRate;
			$cart["products"][$key]['quantity'] = 1;
			$cart["products"][$key]['product_id'] = $tmp['id'];
			$cart["products"][$key]['qty_multiplier'] = 1;
			$cart["products"][$key] = Ecom::lineValue($cart['products'][$key]);
		}

		//
		// every item potentially has a fuel surcharge
		//
		$quote["fuelCharge"] = 0;
		foreach($cart["products"] as $k=>$p) {
			if ($p["custom_has_fuel_surcharge"]) {
				$quote["fuelCharge"] += $p['price'] * $p["quantity"];
				$this->logMessage(__FUNCTION__,sprintf("adding fuel surcharge for [%s] charge is now [%s] rate [%s]", print_r($p,true), $quote["fuelCharge"], $quote["fuelRate"]),3);
			} else {
			 	$this->logMessage(__FUNCTION__,sprintf("skipping fuel surcharge for [%s]", $p["code"]),3);
			}
		}
		$quote["fuelCharge"] = $quote["fuelCharge"] * $quote["fuelRate"];
		if ($quote["fuelCharge"] != 0) {
			$tmp = $this->fetchSingle(sprintf("select * from product where id = %d",$group["custom_fuel"]));
			$key = $tmp["id"]."|0|0|0";
			$cart = $this->initPriceLine($cart,$key,$tmp);
			$cart['products'][$key]['url'] = $this->getUrl('product',$tmp['id'],$tmp);
			$cart['products'][$key]['line_id'] = count($cart['products']);
			$cart['products'][$key]['price'] = $quote["fuelCharge"];
			$cart['products'][$key]['regularPrice'] = $quote["fuelCharge"];;
			$cart['products'][$key]['value'] = $quote["fuelCharge"];
			$cart['products'][$key]['quantity'] = 1;
			$cart['products'][$key]['product_id'] = $tmp['id'];
			$cart['products'][$key]['qty_multiplier'] = 1;
			$cart["products"][$key] = Ecom::lineValue($cart['products'][$key]);
		}

		//
		//	check for per unit charges
		//
		$g_qty = 0;
		foreach($cart["products"] as $k=>$v) {
			if ($v["custom_package"] == "P") {
				if ($chg = $this->fetchSingle(sprintf("select * from member_package_charges where member_id = %d and product_id = %d", $this->getUserInfo("id", true), $v["id"]))) {
					$qty = $v["quantity"] - $chg["free"];
					if ($qty > 0) {
						$tmp = $this->fetchSingle(sprintf("select * from product where id = %d",ADDITIONAL_PIECE_CHARGES));
						//
						//	Fudge key as we can now have multiple per piece charges
						//
						$key = $tmp["id"]."|0|0|".$tmp["id"];
						$cart = $this->initPriceLine($cart,$key,$tmp);
						$cart["products"][$key]['url'] = $this->getUrl('product',$tmp['id'],$tmp);
						$cart["products"][$key]['line_id'] = count($cart['products']);
						$cart["products"][$key]['price'] = $chg["additional_fee"];
						$cart["products"][$key]['regularPrice'] = $chg["additional_fee"];
						$cart["products"][$key]['value'] = $chg["additional_fee"] * $qty;
						$cart["products"][$key]['quantity'] = $qty;
						$cart["products"][$key]['product_id'] = $tmp['id'];
						$cart["products"][$key]['qty_multiplier'] = 1;
						$cart["products"][$key] = Ecom::lineValue($cart['products'][$key]);
					}
				}
				else $g_qty += $v["quantity"];
			}
		}
		if ($member["custom_free_item_count"] > 0 && $g_qty > $member["custom_free_item_count"]) {
			$tmp = $this->fetchSingle(sprintf("select * from product where id = %d",ADDITIONAL_PIECE_CHARGES));
			$g_qty -= $member["custom_free_item_count"];
			$key = $tmp["id"]."|0|0|0";
			$cart = $this->initPriceLine($cart,$key,$tmp);
			$cart["products"][$key]['url'] = $this->getUrl('product',$tmp['id'],$tmp);
			$cart["products"][$key]['line_id'] = count($cart['products']);
			$cart["products"][$key]['price'] = $member["custom_free_item_charge"];
			$cart["products"][$key]['regularPrice'] = $member["custom_free_item_charge"];
			$cart["products"][$key]['value'] = $member["custom_free_item_charge"] * $g_qty;
			$cart["products"][$key]['quantity'] = $g_qty;
			$cart["products"][$key]['product_id'] = $tmp['id'];
			$cart["products"][$key]['qty_multiplier'] = 1;
			$cart["products"][$key] = Ecom::lineValue($cart['products'][$key]);
		}

		//
		//	2021/07/27 - as per Victor, if both extra weight & extra pieces are being charged, only charge for the more expensive item
		//
		$hasWeight = array("id"=>0,"value"=>0);
		$hasPieces = array("id"=>0,"value"=>0);
		foreach($cart["products"] as $k=>$v) {
			if ($v["product_id"] == ADDITIONAL_PIECE_CHARGES) {
				$hasPieces["value"] = $v["total"];
				$hasPieces["id"] = $k;
			}
			if ($v["product_id"] == $group["custom_weight"]) {
				$hasWeight["value"] = $v["total"];
				$hasWeight["id"] = $k;
			}
		}
		$this->logMessage(__FUNCTION__,sprintf("*** hasWeight [%s] has Pieces [%s]", print_r($hasWeight,true), print_r($hasPieces,true)),1);
		if ($hasWeight["value"] >= .01 && $hasPieces["value"] >= .01) {
			if ($hasPieces["value"] > $hasWeight["value"]) {
				$cart["products"][$hasWeight["id"]]["price"] = 0;
				$cart["products"][$hasWeight["id"]]['regularPrice'] = 0;
				$cart["products"][$hasWeight["id"]]['value'] = 0;
				$cart["products"][$hasWeight["id"]] = Ecom::lineValue($cart['products'][$hasWeight["id"]]);
			}
			else {
				$cart["products"][$hasPieces["id"]]["price"] = 0;
				$cart["products"][$hasPieces["id"]]['regularPrice'] = 0;
				$cart["products"][$hasPieces["id"]]['value'] = 0;
				$cart["products"][$hasPieces["id"]] = Ecom::lineValue($cart['products'][$hasPieces["id"]]);
			}
		}
		$cart = Ecom::recalcOrder($cart);
		$cart = $this->resortProducts($cart);
		$cart["quote"] = $quote;
		$_SESSION["cart"] = $cart;
		$this->logMessage(__FUNCTION__, sprintf("returning quote [%s]", print_r($quote,true)),2);
		return $quote;
	}

	public function calcDelivery($pickup,$product) {
		if (!(is_array($product) && array_key_exists("id",$product) && $product["id"] > 0)) {
			$this->logMessage(__FUNCTION__,sprintf("invalid product passed [%s] pickup [%s]", print_r($product,true), print_r($pickup,true)),1,true,true);
		}
		$p = $this->fetchSingle(sprintf("select * from product where id = %d",$product["id"]));
		if ($p["custom_delivery_relative"])
			$dt = strtotime(sprintf("%s %s",$pickup,$p["custom_delivery_formula"]));
		else
			$dt = strtotime(sprintf("%s %s",date("Y-m-d",strtotime($pickup)),$p["custom_delivery_formula"]));
		$this->logMessage(__FUNCTION__,sprintf("calced [%s] from [%s] [%s]",date("Y-m-d H:i:s",$dt),$pickup,$p["custom_delivery_formula"]),1);
		if ($p["custom_same_day"] == 0) {
			$dow = date("w",$dt);
			$i = 0;
			$tmp = $this->fetchSingle(sprintf("select * from order_processing where bill_date = '%s'", date('Y-m-d',$dt)));
			while($i < 10 && (false == $this->fetchSingle(sprintf("select * from order_processing where bill_date = '%s'", date('Y-m-d',$dt))) || ($p["custom_availability"] & pow(2,$dow)) == 0)) {
				$this->logMessage(__FUNCTION__,sprintf("bumping date i [%d] mask [%d]", $i, $p["custom_availability"] & pow(2,$dow)),3);
				$dt += 24*60*60;
				$dow = date("w",$dt);
				$i += 1;	// failsafe
				$this->logMessage(__FUNCTION__,sprintf("date [%s] dow [%d] available [%d] from [%d]",date(DATE_ATOM,$dt), $dow, pow(2,$dow) & $p["custom_availability"],$p["custom_availability"]),3);
			};
			$dt = date("Y-m-d H:i:s",$dt);
			$this->logMessage(__FUNCTION__,sprintf("pickup is [%s] delivery is [%s] from [%s]",$pickup,$dt,$p["custom_delivery_formula"]),2);
		}
		else {
			$dt = date("Y-m-d H:i:s",$dt);
			$this->logMessage(__FUNCTION__, sprintf("delivery date not modified (%s)", $dt),1);
		}
		return $dt;
	}

	private function initPriceLine($cart,$key,$product) {
		$this->logMessage(__FUNCTION__,sprintf("adding [%s] to cart", print_r($product,true)),3);
		if (strpos($key,"|")===false) $this->logMessage(__FUNCTION__,sprintf("invalid product key [%s] product [%s]", $key, print_r($product,true)),3,true);
		$test = explode("|",$key);
		if ((int)$test == 0) {
			$this->logMessage(__FUNCTION__,sprintf("bad product data [%s] product [%s] in cart [%s]", $key, print_r($product,true), print_r($cart,true)),3,true);
		}
		else {
			$cart["products"][$key] = $product;
			$cart["products"][$key]["product_id"] = $product["id"];
			$cart['products'][$key]['price'] = 0;
			$cart['products'][$key]['regularPrice'] = 0;
			$cart['products'][$key]['value'] = 0;
			$cart['products'][$key]['quantity'] = 1;
			$cart['products'][$key]['discount_value'] = 0;
			$cart['products'][$key]['recurring_discount_value'] = 0;
			$cart['products'][$key]['shipping'] = 0;
			$cart['products'][$key]['options_id'] = 0;
			$cart['products'][$key]['recurring_period'] = 0;
			$cart['products'][$key]['qty_multiplier'] = 1;
			$cart['products'][$key]['discount_type'] = '';
			$cart['products'][$key]['color'] = 0;
			$cart['products'][$key]['size'] = 0;
			$cart['products'][$key]['shipping_only'] = 0;
			$cart['products'][$key]['recurring_shipping_only'] = 0;
			$cart['products'][$key]['recurring_discount_type'] = 0;
			$cart['products'][$key]['recurring_discount_rate'] = 0;
			$cart['products'][$key]['recurring_qty'] = 0;
			$cart['products'][$key]['coupon_id'] = 0;
			$cart['products'][$key]['discount_rate'] = 0;
			$cart['products'][$key]['inventory_id'] = 0;
			$cart['products'][$key]['custom_package'] = "A";
			$cart['products'][$key]['custom_weight'] = 0;
			$cart['products'][$key]['taxes'] = 0;
			$cart['products'][$key]['total'] = 0;
			$cart['products'][$key]['taxdata'] = array();
		}
		return $cart;
	}

	private function resortProducts($cart) {
		//
		//	resort the products into Service, Package, Additional charges for display purposes
		//
		$p = array();
		$s = array();
		$a = array();
		foreach($cart["products"] as $key=>$product) {
			switch($product["custom_package"]) {
			case "P":
				$p[$key] = $product;
				break;
			case "A":
				$a[$key] = $product;
				break;
			case "S":
				$s[$key] = $product;
				break;
			}
			$tmp = array();
			$id = 0;
			foreach($s as $key=>$product) {
				$id += 1;
				$product["line_id"] = $id;
				$tmp[$key] = $product;
			}
			foreach($p as $key=>$product) {
				$id += 1;
				$product["line_id"] = $id;
				$tmp[$key] = $product;
			}
			foreach($a as $key=>$product) {
				$id += 1;
				$product["line_id"] = $id;
				$tmp[$key] = $product;
			}
			$cart["products"] = $tmp;
		}
		return $cart;
	}

	function getFedEx($cart,$quote) {
		return $this->fedExRates( $cart, $quote );
	}

	public function insertOrder($cart,&$orderid) {
		$this->logMessage(__FUNCTION__,sprintf("cart [%s]", print_r($cart,true)),1);
		$reauthorizing = array_key_exists('reauthorize',$cart) && array_key_exists('o_id',$cart['reauthorize']) && $cart['reauthorize']['o_id'] > 0;
		$stmt = $this->prepare('insert into orders(member_id, order_status, value, coupon_id, discount_value, discount_rate, line_discounts, net, shipping, taxes, total, order_date, created, random, discount_type, recurring_period, currency_id, exchange_rate, ship_via, authorization_code, authorization_transaction, handling, points_redeemed, points_collected) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
		$stmt->bindParams(array('iididddddddssisiidsssddd', 
			$cart['header']['member_id'] > 0 ? $cart['header']['member_id'] : $_SESSION['user']['info']['id'], 
			STATUS_INCOMPLETE | ($reauthorizing ? (STATUS_REAUTHORIZING | STATUS_RECURRING) : 0), $cart['header']['value'], $cart['header']['coupon_id'], $cart['header']['discount_value'], $cart['header']['discount_rate'], 
			$cart['header']['line_discounts'], $cart['header']['net'], $cart['header']['shipping'], $cart['header']['taxes'], $cart['header']['total'], 
			date(DATE_ATOM), date(DATE_ATOM), rand(), $cart['header']['discount_type'],
			array_key_exists('recurring_period',$cart['header']) ? $cart['header']['recurring_period'] : 0,
			array_key_exists('currency_id',$cart['header'])?$cart['header']['currency_id']:0, 
			array_key_exists('exchange_rate',$cart['header'])?$cart['header']['exchange_rate']:0,
			array_key_exists('ship_via',$cart['header'])?$cart['header']['ship_via']:"",
			$reauthorizing ? sprintf('Reauthorizing Order #%d',$this->fetchScalar(sprintf('select authorization_transaction from orders where id = %d',$cart['reauthorize']['o_id']))) : "",
			$reauthorizing ? $cart['reauthorize']['o_id'] : "", $cart['header']['handling'], $cart['header']['points_redeemed'], $cart['header']['points_collected']));
		if ($valid = $stmt->execute()) {
			$orderid = $this->insertId();
			$idx = 0;
			//$valid = true;
			foreach($cart['products'] as $key=>$line) {
				$idx += 1;
				$stmt = $this->prepare('insert into order_lines(order_id,line_id,product_id,options_id,quantity,price,coupon_id,discount_value,discount_rate,discount_type,value,shipping,inventory_id,total,taxes,tax_exemptions,color,size,recurring_discount_rate,recurring_discount_value,recurring_shipping_only,recurring_discount_type,recurring_period,recurring_qty,qty_multiplier,custom_package) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
				$stmt->bindParams(array('iiiiididddddiddsddddisddds',$orderid,$idx,$line['product_id'],$line['options_id'],$line['quantity'],$line['price'],$line['coupon_id'],$line['discount_value'],$line['discount_rate'],$line['discount_type'],$line['value'],$line['shipping'],$line['inventory_id'],$line['total'],$line['taxes'],$line['tax_exemptions'],
					$line['color'],$line['size'],$line['recurring_discount_rate'], $line['recurring_discount_value'], $line['recurring_shipping_only'], $line['recurring_discount_type'],
					$line['recurring_period'], $line['recurring_qty'],$line['qty_multiplier'],$line["custom_package"]));
				$valid = $valid && $stmt->execute();
				$line_id = $this->insertId();
				foreach($line['taxdata'] as $taxkey=>$taxline) {
					$tax = $this->prepare('insert into order_taxes(order_id,line_id,tax_id,tax_amount,taxable_amount) values(?,?,?,?,?)');
					$tax->bindParams(array('iiidd',$orderid,$idx,$taxkey,$taxline['tax_amount'],$taxline['taxable_amount']));
					$valid = $valid && $tax->execute();
				}
				if (array_key_exists("dimensions",$line)) {
					foreach($line["dimensions"] as $skey=>$dim) {
						$this->logMessage(__FUNCTION__,sprintf("dim is [%s]",print_r($dim,true)),1);
						$stmt = $this->prepare("insert into order_lines_dimensions(order_id, line_id, weight, height, width, depth, quantity) values(?, ?, ?, ?, ?, ?, ?)");
						$stmt->bindParams(array("iiddddd",$orderid, $idx,$dim["weight"],$dim["height"],$dim["width"],$dim["depth"],$dim["quantity"]));
						$valid = $valid && $stmt->execute();
					}
				}
			}
			foreach($cart['addresses'] as $key=>$address) {
				if (is_array($address) && array_key_exists('ownerid',$address) && $address['ownerid'] > 0) {
					$address['ownertype'] = 'order';
					$address['ownerid'] = $orderid;
					$address['addressbook_id'] = $address['id'];
					unset($address['id']);
					$stmt = $this->prepare(sprintf('insert into addresses(%s) values(%s)',implode(',',array_keys($address)),str_repeat('?,',count($address)-1).'?'));
					$stmt->bindParams(array_merge(array(str_repeat('s',count($address))),array_values($address)));
					$valid = $valid && $stmt->execute();
				}
			}
			foreach($cart['taxes'] as $taxkey=>$taxline) {
				$tax = $this->prepare('insert into order_taxes(order_id,line_id,tax_id,tax_amount,taxable_amount) values(?,?,?,?,?)');
				$tax->bindParams(array('iiidd',$orderid,0,$taxkey,$taxline['tax_amount'],$taxline['taxable_amount']));
				$valid = $valid && $tax->execute();
			}
		}
		$this->logMessage(__FUNCTION__,sprintf('returning state [%s]',$valid),1);
		return $valid;
	}

	function finalizeOrder($orderId,$valid,$caller) {
		if ($orderId > 0) {
			if ($mgmt = $this->checkArray("mgmt:user:id",$_SESSION)) {
				$this->execute(sprintf("update orders set login_id = %d where id = %d", $mgmt, $orderId ));
			}
//
//	Some sanity checks to find issues
//
			if ($this->fetchSingle(sprintf("select o.id from orders o, product p, order_lines ol, custom_delivery cd where o.id = %d and ol.order_id = o.id and p.id = ol.product_id and p.custom_same_day = 0 and ol.custom_package='S' and cd.order_id = o.id and cd.service_type='D' and cd.driver_id = 0", $orderId))) {
				$this->logMessage(__FUNCTION__,sprintf("no delivery driver assigned [%s]", $orderId),1,true,false);
			}
			if (!$this->fetchSingle(sprintf("select * from order_lines where custom_package = 'S' and order_id = %d", $orderId))) {
				$this->logMessage(__FUNCTION__,sprintf("no service type [%s]", $orderId),1,true,false);
			}
			$cart = $_SESSION["cart"];
			$quote = array_key_exists("quote",$_SESSION) ? $_SESSION["quote"] : array();
			//
			//	grab the fuel rate and save it - if the rate changes we can use the rate as of the order date
			//
			if ($this->checkArray("quote:fuelRate",$cart) && $cart["quote"]["fuelRate"] > .01) {
				$this->execute(sprintf("update orders set custom_fuel_rate = %f where id = %d", $cart["quote"]["fuelRate"], $orderId));
			}
			$tmp = array();
			foreach($cart["products"] as $key=>$p) {
				if ($p["custom_package"] == "S") $tmp = $p;
			}
			$this->logMessage(__FUNCTION__,sprintf("service is set to [%s]",print_r($tmp,true)),1);
			if (!($this->checkArray("mgmt:user:custom_super_user",$_SESSION) && $_SESSION["mgmt"]["user"]["custom_super_user"] == 1))
				$cart["header"]["pickup_datetime"] = max(date("Y-m-d H:i:s", strtotime($cart["header"]["pickup_datetime"])),date("Y-m-d H:i:s"));

//
//	recurring order check - can assign specific drivers to p/u & deliver
//
			if (array_key_exists("custom_recurring_pu_driver",$quote) && $quote["custom_recurring_pu_driver"] > 0) {
				$cart["quote"]["pickup_driver"] = $this->fetchSingle(sprintf("select * from drivers where id = %d",$quote["custom_recurring_pu_driver"]));
			}
			if (array_key_exists("custom_recurring_del_driver",$quote) && $quote["custom_recurring_del_driver"] > 0) {
				$cart["quote"]["delivery_driver"] = $this->fetchSingle(sprintf("select * from drivers where id = %d",$quote["custom_recurring_del_driver"]));
			}

			$delivery = array("order_id"=>$orderId,"service_type"=>"P","scheduled_date"=>$cart["header"]["pickup_datetime"],"driver_sequence"=>9999);
			$delivery["driver_id"] = array_key_exists("quote",$cart) && array_key_exists("pickup_driver",$cart["quote"]) ? $cart["quote"]["pickup_driver"]["id"] : 0;
			$pu_driver = $delivery["driver_id"];
			$del_driver = array_key_exists("quote",$cart) && array_key_exists("delivery_driver",$cart["quote"]) ? $cart["quote"]["delivery_driver"]["id"] : 0;
			if ($pu_driver == $del_driver)
				$delivery["percent_of_delivery"] = 50;
			else
				$delivery["percent_of_delivery"] = 25;
			if (array_key_exists("pickupInstructions",$cart["header"]) && strlen($cart["header"]["pickupInstructions"]) > 0)
				$delivery["instructions"] = $cart["header"]["pickupInstructions"];
			elseif (array_key_exists("pickupInstructions",$quote) && strlen($quote["pickupInstructions"]) > 0)
				$delivery["instructions"] = $quote["pickupInstructions"];
			$delivery["ack_requested"] = date(DATE_ATOM);
			$delivery["dispatch_message"] = "New order placed";
			$stmt = $this->prepare(sprintf("insert into custom_delivery(%s) values(%s?)", implode(",",array_keys($delivery)),str_repeat("?, ",count($delivery)-1)));
			$stmt->bindParams(array_merge(array(str_repeat("s",count($delivery))),array_values($delivery)));
			$stmt->execute();
			$p_id = $this->insertId();
			$pickup = $delivery;
			$delivery["service_type"] = "D";
			$delivery["instructions"] = "";
			if (array_key_exists("deliveryInstructions",$cart["header"]) && strlen($cart["header"]["deliveryInstructions"]) > 0)
				$delivery["instructions"] = $cart["header"]["deliveryInstructions"];
			elseif (array_key_exists("deliveryInstructions",$quote) && strlen($quote["deliveryInstructions"]) > 0)
				$delivery["instructions"] = $quote["deliveryInstructions"];

			$delivery["scheduled_date"] = "";
			foreach($cart["products"] as $k1=>$v1) {
				if ($v1["is_fedex"]) {
					foreach($_SESSION["cart"]["custom"]["rates"] as $k2 =>$v2) {
						$this->logMessage(__FUNCTION__,sprintf("test 2 v2 [%s] code [%s]", print_r($v2,true), $v1["code"]), 1);
						if ($v2["code"] == $v1["code"]) {
							if ($v2["expectedDelivery"] != "") {
								$delivery["scheduled_date"] = $v2["expectedDelivery"];
							}
						}
					}
				}
			}
			if ($delivery["scheduled_date"] == "") {
				$kjv = new KJV();
				$delivery["scheduled_date"] = $kjv->calcDelivery($_SESSION["cart"]["header"]["pickup_datetime"],$tmp);
			}
			$delivery["driver_id"] = $del_driver;
			if ($pu_driver == $del_driver)
				$delivery["percent_of_delivery"] = 50;
			else
				$delivery["percent_of_delivery"] = 75;
			$stmt = $this->prepare(sprintf("insert into custom_delivery(%s) values(%s?)", implode(",",array_keys($delivery)),str_repeat("?, ",count($delivery)-1)));
			$stmt->bindParams(array_merge(array(str_repeat("s",count($delivery))),array_values($delivery)));
			$stmt->execute();
			$d_id = $this->insertId();
			$seq = 0;
$this->calc_driver_allocations($orderId);
			$custom = array(
				"custom_weight_code"=>$cart["header"]["custom_weight_code"],
				"custom_placed_by"=>$this->checkArray("quote:custom_placed_by",$_SESSION) ? $_SESSION["quote"]["custom_placed_by"] : "",
				"custom_email_confirmation"=>$this->checkArray("quote:custom_email_confirmation",$_SESSION) ? $_SESSION["quote"]["custom_email_confirmation"] : "",
				"custom_pickup_email"=>$this->checkArray("quote:custom_pickup_email",$_SESSION) ? $_SESSION["quote"]["custom_pickup_email"] : "",
				"custom_dimension_code"=>$cart["header"]["custom_dimension_code"],
				"custom_declared_value"=>$cart["header"]["custom_declared_value"],
				"custom_reference_number"=>$cart["header"]["custom_reference_number"],
				"custom_recurring_pu_driver"=>$this->checkArray("quote:custom_recurring_pu_driver",$_SESSION) ? $_SESSION["quote"]["custom_recurring_pu_driver"] : 0,
				"custom_recurring_del_driver"=>$this->checkArray("quote:custom_recurring_del_driver",$_SESSION) ? $_SESSION["quote"]["custom_recurring_del_driver"] : 0,
				"custom_signature_required"=>$this->checkArray("quote:custom_signature_required",$_SESSION) ? $_SESSION["quote"]["custom_signature_required"] : 0
			);
			$stmt = $this->prepare(sprintf("update orders set %s=? where id = %d", implode("=?, ",array_keys($custom)),$orderId));
			$stmt->bindParams(array_merge(array(str_repeat("s",count($custom))),array_values($custom)));
			$stmt->execute();

//
//	Dont send texts if its the nightly recurring process
//
			if (!(array_key_exists("fromNightly",$quote) && $quote["fromNightly"]==1)) {
				$hours = $this->getConfigVar("normalHours","config");
				$hr_pts = explode(",",$hours);
				$time = explode("-",$hr_pts[0]);
				$days = explode("-",$hr_pts[1]);
				$pu_dt = strtotime($pickup["scheduled_date"]);
				$this->logMessage(__FUNCTION__, sprintf("pu_dt [%s] w [%s] H [%s]", $pickup["scheduled_date"], date("w", $pu_dt), date("H", $pu_dt)),1);
				if ((int)date("w", $pu_dt) < $days[0] || (int)date("w", $pu_dt) > $days[1] || (int)date("H", $pu_dt) < $time[0] || (int)date("H", $pu_dt) > $time[1]) {
					$this->logMessage(__FUNCTION__,sprintf("calling sendAlert"),1);
					$this->sendAlert( $p_id, $this->m_dir."sendAlert.html", $this->getFields("sendAlert"));
				}
				$this->sendText( $p_id, $this->m_dir."sendText.html", $this->getFields("sendText"));
				if ($pu_driver != $del_driver)
					$this->sendText( $d_id, $this->m_dir."sendText.html", $this->getFields("sendText"));
			}
			$this->calculateCommissions($orderId);
			$this->logMessage(__FUNCTION__,sprintf("recurring check cart [%s]", print_r($cart,true)),1);
			if (array_key_exists("recurring",$cart) && is_array($cart["recurring"]) && count($cart["recurring"]) > 0 && $cart["recurring"]["frequency"] > 0) {
				$this->setRecurring($orderId,$cart);
			}
		}
		if (array_key_exists('cart',$_SESSION) && array_key_exists('abandoned',$_SESSION['cart'])) {
			if (array_key_exists('id',$_SESSION['cart']['abandoned'])) {
				$this->execute(sprintf('delete from cart_header where id = %d',$_SESSION['cart']['abandoned']['id']));
				$this->execute(sprintf('delete from cart_lines where order_id = %d',$_SESSION['cart']['abandoned']['id']));
			}
			unset($_SESSION['cart']['abandoned']);
		}
		if (array_key_exists('quote',$_SESSION)) unset($_SESSION["quote"]);
/*
		$orderHtml = "";
		$emails = $this->configEmails("ecommerce");
		if (count($emails) == 0)
			$emails = $this->configEmails("contact");
		$this->logMessage('postSaleProcessing',sprintf('notifying on order [%d] status [%s] emails [%s] caller [%s]',$orderId,$valid,print_r($emails,true),print_r($caller,true)),1);
		$body = new Forms();
		$mailer = new MyMailer();
		$mailer->Subject = sprintf("Order Processing - %s", SITENAME);
		$body = new Forms();
		$sql = sprintf('select * from htmlForms where class = %d and type = "orderEmail"',$this->getClassId('product'));
		$html = $this->fetchSingle($sql);
		$body->setHTML($html['html']);
		if (!$order = $this->fetchSingle(sprintf('select o.*, m.firstname, m.lastname, m.email from orders o, members m where o.id = %d and m.id = o.member_id',$orderId)))
			$this->logMessage(__FUNCTION__,sprintf('cannot locate order #[%d]',$orderId),1,true);
		$body->addData($this->formatOrder($order));
		if ($caller->hasOption('receiptPrint') && $module = $this->fetchSingle(sprintf('select t.*, m.classname, t.id as fetemplate_id from fetemplates t, modules m where t.id = %d and m.id = t.module_id',$caller->getOption('receiptPrint')))) {
			$this->logMessage('postSaleProcessing',sprintf('caller module [%s] this [%s]',print_r($module,true),print_r($this,true)),4);
			$class = new $module['classname']($module['id'],$module);
			$orderHtml = $class->{$module['module_function']}();
			$body->addTag('order',$orderHtml,false);
		}
		if ($this->hasOption('receiptPrint') && $module = $this->fetchSingle(sprintf('select t.*, m.classname from fetemplates t, modules m where t.id = %d and m.id = t.module_id',$this->getOption('receiptPrint')))) {
			$this->logMessage('postSalePrcessing',sprintf('this module [%s] this [%s]',print_r($module,true),print_r($this,true)),2);
			$class = new $module['classname']($module['id'],$module);
			$orderHtml = $class->{$module['module_function']}();
			$body->addTag('order',$orderHtml,false);
		}
		$body->setOption('formDelimiter','{{|}}');
		$mailer->Body = $body->show();
		$mailer->From = $order['email'];
		$mailer->FromName = $order['firstname'].' '.$order['lastname'];
		$mailer->IsHTML(true);
		foreach($emails as $key=>$value) {
			$mailer->addAddress($value['email'],$value['name']);
		}
		if (!$mailer->Send()) {
			$this->logMessage('postSaleProcessing',sprintf("Email send failed [%s]",print_r($mailer,true)),1,true);
		}
*/
		if (($this->hasOption('userEmail') || $caller->hasOption('userEmail')) && strlen($orderHtml) > 0) {
			/*
				User Email here
			*/
			$this->logMessage(__FUNCTION__,'sending user receipt email',3);
			$mailer = new MyMailer();
			$mailer->Subject = sprintf("Your Order Receipt - %s", SITENAME);
			$body = new Forms();
			$sql = sprintf('select * from htmlForms where class = %d and type = "userEmail"',$this->getClassId('product'));
			$html = $this->fetchSingle($sql);
			$body->setHTML($html['html']);
			$body->addData($this->formatOrder($order));
			$body->addTag('order',$orderHtml,false);
			$body->setOption('formDelimiter','{{|}}');
			$mailer->Body = $body->show();
			$mailer->From = $emails[0]['email'];
			$mailer->FromName = $emails[0]['name'];
			$mailer->IsHTML(true);	
			$mailer->addAddress($order['email'],$order['firstname'].' '.$order['lastname']);
			if (!$mailer->Send()) {
				$this->logMessage(__FUNCTION__,sprintf("User Email send failed [%s]",print_r($mailer,true)),1,true);
			}
		}
	}

	function getWalkingDistance($addresses) {
		$src = sprintf("%s, %s %s %s %s", $addresses["pickup"]["line1"], 
			$addresses["pickup"]["city"], $this->fetchScalar(sprintf("select province_code from provinces where id = %d", $addresses["pickup"]["province_id"])), $addresses["pickup"]["postalcode"], $this->fetchScalar(sprintf("select country from countries where id = %d", $addresses["pickup"]["country_id"])));
		$dest = sprintf("%s, %s %s %s %s", $addresses["shipping"]["line1"],
			$addresses["shipping"]["city"], $this->fetchScalar(sprintf("select province_code from provinces where id = %d", $addresses["shipping"]["province_id"])), $addresses["shipping"]["postalcode"], $this->fetchScalar(sprintf("select country from countries where id = %d", $addresses["shipping"]["country_id"])));
		$req = sprintf("https://maps.googleapis.com/maps/api/distancematrix/json?origins=%s&destinations=%s",urlencode($src), urlencode($dest));
		$s = new Snoopy();
		$s->host = 'https://maps.googleapis.com/maps/api/geocode/json';
		$s->port = 443;
		$s->curl_path = $GLOBALS['curl_path'];
/*
*
*			Use a walking route to get the shortest distance for billing purposes - if no walking route request driving
*
*/
		//$key = sprintf('https://maps.googleapis.com/maps/api/distancematrix/json?origins=%s&destinations=%s&sensor=false&key=%s',
		$key = sprintf('https://maps.googleapis.com/maps/api/distancematrix/json?mode=walking&origins=%s&destinations=%s&sensor=false&key=%s',
				urlencode($src), urlencode($dest),$this->getConfigVar("google_maps_key"));
		$s->fetch($key);
		$result = json_decode($s->results,true);
		$distance = 9999;
		$this->logMessage(__FUNCTION__,sprintf("snoopy result is [%s] from [%s] [%s]",$result["status"],$key,print_r($s,true)),2);
		if ($result["status"] == "OK" && $result["rows"][0]["elements"][0]["status"] != "NOT_FOUND") {
			$this->logMessage(__FUNCTION__,sprintf("decoded is [%s]",print_r($result,true)),2);
			if (!$this->checkArray("rows:0:elements:0:distance",$result)) {
				$key = sprintf('https://maps.googleapis.com/maps/api/distancematrix/json?origins=%s&destinations=%s&sensor=false&key=%s',
						urlencode($src), urlencode($dest),$this->getConfigVar("google_maps_key"));
				$s->fetch($key);
				$result = json_decode($s->results,true);
				$this->logMessage(__FUNCTION__,sprintf("non-walking snoopy result is [%s] from [%s] [%s]",$result["status"],$key,print_r($s,true)),2);
			}
			if (!$this->checkArray("rows:0:elements:0:distance",$result)) {
				$this->addEcomError("We were unable to calculate the distance");
				$this->addEcomError(sprintf("Pickup Address: %s", $result["origin_addresses"][0]));
				$this->addEcomError(sprintf("Delivery Address: %s", $result["destination_addresses"][0]));
				$distance=9999;
				$this->logMessage(sprintf("address calculation failed result [%s] from [%s]", print_r($result,true), $key),1,true);
			}
			else {
				$distance = max(1,round($result["rows"][0]["elements"][0]["distance"]["value"] / 1000,0));
			}
		}		
		return $distance;
	}

	function getKmRate(&$quote, &$r, &$kmProduct, $group, $product, $member, $distance, $pickup, $shipto, $m_override, $g_override, $in_zone) {
		//
		//	New km incremental by km rates
		//
		//	min charge = 1st/lowest km charge
		//	keep charging incremental rates until we reach the distance required'
		//
		$j_id = $this->fetchScalar(sprintf("select id from members_by_folder where member_id = %d and folder_id = %d", $member["id"], $group["id"]));
		$grp_inc = $this->fetchAll(sprintf("select km.*, kmo.in_zone_override, kmo.out_of_zone_override from custom_product_km_ranges km left join custom_product_km_range_overrides kmo on kmo.km_opt_id = km.id and kmo.junction_id = %d, custom_member_product_options cpo where cpo.member_id = %d and cpo.isgroup = 1 and cpo.product_id = %d and km.member_product_option_id = cpo.id order by km_max",$j_id,$group["id"],$product["id"]));
		$mbr_inc = $this->fetchAll(sprintf("select km.* from custom_product_km_ranges km, custom_member_product_options cpo, members_by_folder mf where mf.member_id = %d and mf.folder_id = %d and cpo.member_id = mf.id and cpo.isgroup = 0 and cpo.product_id = %d and km.member_product_option_id = cpo.id order by km_max",$member["id"],$group["id"],$product["id"]));
		$kmRate = 0;
		$quote["kmCharge"] = max($distance,1);
		$quote["kmRate"] = 0;
		$r["rate"] = 0;
		$r["qty"] = 0;
		$r["charge"] = 0;
		$kmOverride = false;
		if (count($mbr_inc) > 0) {
			$used = 0;
			$p_max = 0;
			$balance = $distance;
			foreach($mbr_inc as $gk=>$gv) {
				$km = min($gv["km_max"]-$p_max,$balance);
				$balance -= $km;
				if ($in_zone || $gv["out_of_zone"] < 0.01)
					$quote["kmRate"] += $km * $gv["price"];
				else
					$quote["kmRate"] += $km * $gv["out_of_zone"];
				$p_max = $gv["km_max"];
			}
			if ($balance > 0 && count($mbr_inc)) {
				//
				//	distance greater than allowed for, use the last rate
				//
				if ($in_zone || $mbr_inc[count($mbr_inc)-1]["out_of_zone"] < .01)
					$quote["kmRate"] += $balance * $mbr_inc[count($mbr_inc)-1]["price"];
				else
					$quote["kmRate"] += $balance * $mbr_inc[count($mbr_inc)-1]["out_of_zone"];
			}
			//
			//	right now, kmRate is the total charge, recalc to a blended /km rate
			//
			$quote["price"] = $quote["kmRate"];
			$quote["kmRate"] = round($quote["kmRate"] / $distance,2);
			$quote["kmCharge"] = $distance;
			$r["rate"] = $quote["kmRate"];
			$r["qty"] = $quote["kmCharge"];
			$r["charge"] = round($r["rate"]*$r["qty"],2);
		}
		elseif (count($grp_inc) > 0) {
			$used = 0;
			$p_max = 0;
			$balance = $distance;
			foreach($grp_inc as $gk=>$gv) {
				$km = min($gv["km_max"]-$p_max,$balance);
				$balance -= $km;
				if ($in_zone)
					$price = max(0,round($gv["price"] * (1+$gv["in_zone_override"]/100),2));
				else
					$price = max(0,round($gv["out_of_zone"] * (1+$gv["out_of_zone_override"]/100),2));
				$quote["kmRate"] += $km * $price;
				$p_max = $gv["km_max"];
			}
			if ($balance > 0 && count($grp_inc)) {
				//
				//	distance greater than allowed for, use the last rate
				//
				$gv = $grp_inc[count($grp_inc)-1];
				if ($in_zone)
					$price = max(0,round($gv["price"] * (1+$gv["in_zone_override"]/100),2));
				else
					$price = max(0,round($gv["out_of_zone"] * (1+$gv["out_of_zone_override"]/100),2));
				$quote["kmRate"] += $balance * $price;
			}
			//
			//	right now, kmRate is the total charge, recalc to a blended /km rate
			//
			$quote["price"] = $quote["kmRate"];
			$quote["kmRate"] = round($quote["kmRate"] / $distance,2);
			$quote["kmCharge"] = $distance;
			$r["rate"] = $quote["kmRate"];
			$r["qty"] = $quote["kmCharge"];
			$r["charge"] = round($r["rate"]*$r["qty"],2);
		}
		else {
			if ($in_zone)
				$kmRate = $product["custom_km_charge"];
			else
				$kmRate = $product["custom_out_of_zone_rate"];
			$kmRate = round($kmRate * (1+($g_override["km_charge"] + (is_array($m_override) ? $m_override["km_charge"] : 0))/100),2);
			$quote["kmCharge"] = max($distance,1);
			$quote["kmRate"] = round($kmRate,2);
			$r["rate"] = $quote["kmRate"];
			$r["qty"] = $quote["kmCharge"];
			$r["charge"] = round($r["rate"]*$r["qty"],2);
			$kmOverride = false;
		}
		//
		//	get source & dest zone, check for min/max overrides
		//

		$src_zone = $this->fetchSingle(sprintf("select z.title, zbf.id
	from zones z, zone_fsa zfsa, fsa, zones_by_folder zbf
	where zbf.folder_id = %d and z.id = zbf.zone_id and zfsa.zone_id = z.id and fsa.fsa = '%s' and zfsa.fsa_id = fsa.id and zfsa.enabled = 1",$_SESSION["user"]["info"]["custom_zones"],
	substr($pickup["postalcode"],0,3)));

		$dest_zone = $this->fetchSingle(sprintf("select z.title, zbf.id
	from zones z, zone_fsa zfsa, fsa, zones_by_folder zbf
	where zbf.folder_id = %d and z.id = zbf.zone_id and zfsa.zone_id = z.id and fsa.fsa = '%s' and zfsa.fsa_id = fsa.id and zfsa.enabled = 1",$_SESSION["user"]["info"]["custom_zones"],
	substr($shipto["postalcode"],0,3)));

		$kmProduct = KM_RATE;

		if (is_array($src_zone) && is_array($dest_zone)) {
			$quote["srcZone"] = sprintf("%s - %d",$src_zone["title"],$src_zone["id"]);
			$quote["dstZone"] = sprintf("%s - %d",$dest_zone["title"],$dest_zone["id"]);
			if ($z_to_z = $this->fetchSingle(sprintf("select * from zone_to_zone where (zone_from = %d and zone_to = %d) or (zone_to = %d and zone_from = %d)",$src_zone["id"],$dest_zone["id"],$src_zone["id"],$dest_zone["id"]))) {
				if ($zone_override = $this->fetchSingle(sprintf("select * from zone_to_zone_caps where zone_to_zone_id = %d and product_id = %d",$z_to_z["id"],$product["id"]))) {
					$kmOverride = true;
					if ($r["charge"] < $zone_override["custom_km_mincharge"]) {
						$kmProduct = KM_MIN;
						$r["rate"] = $zone_override["custom_km_mincharge"];
						$r["qty"] = 1;
					}
					elseif ($r["charge"] > $zone_override["custom_km_maxcharge"] && $zone_override["custom_km_maxcharge"] > 0.01) {
						if ($in_zone) {
							$kmProduct = KM_MAX;
							$r["rate"] = $zone_override["custom_km_maxcharge"];
							$r["qty"] = 1;
						}
					}
				}
			}
		}
$this->logMessage(__FUNCTION__, sprintf("/// kmOverride [%s] r [%s]", $kmOverride, print_r($r,true)),1);
		if (!$kmOverride) {
			$min = $product["custom_km_mincharge"] * (1+($g_override["km_mincharge"] + (is_array($m_override) ? $m_override["km_mincharge"] : 0))/100);
			$min = round($min,2);
			$max = $product["custom_km_maxcharge"] * (1+($g_override["km_maxcharge"] + (is_array($m_override) ? $m_override["km_maxcharge"] : 0))/100);
			$max = round($max,2);
			$quote["kmMin"] = $min;
			$quote["kmMax"] = $max;
			if ($r["charge"] < $min) {
				$kmProduct = KM_MIN;
				$r["rate"] = $min;
				$r["qty"] = 1;
			}
			elseif ($r["charge"] > $max && $max > .01) {
				if ($in_zone) {
					$kmProduct = KM_MAX;
					$r["rate"] = $max;
					$r["qty"] = 1;
				}
			}
			else {
				$kmProduct = KM_RATE;
			}
		}
	}
}

?>
