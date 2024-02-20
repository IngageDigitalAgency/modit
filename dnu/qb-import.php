<?php

require_once("config.php");
require_once(ADMIN."config.php");
require_once(ADMIN."classes/globals.php");
require_once(ADMIN."classes/mailer.php");
require_once(ADMIN."classes/common.php");
require_once(ADMIN."classes/smtp.php");
require_once(ADMIN."classes/Snoopy.php");

$c = new Common(true, false, "qb-import");

$json = file_get_contents("cust-3.json");
$tmp = json_decode($json,true);
//$c->logMessage(__FUNCTION__ ,sprintf("json [%s]", print_r($tmp,true)), 1);
$cust = $tmp["QueryResponse"]["Customer"];
foreach($cust as $k=>$v) {
	//if ($k > 0) continue;
	$valid = true;
	$name = array_key_exists("CompanyName",$v) ? $v["CompanyName"] : $v["DisplayName"];
	if (!array_key_exists("PrimaryEmailAddr",$v)) {
		echo sprintf("%s: missing email", $name).PHP_EOL;
		$valid = false;
	}
	else {
		$email = $v["PrimaryEmailAddr"]["Address"];
		if (!preg_match('/^\S+@[\w\d.-]{2,}\.[\w]{2,6}$/iU', $email)) {
			echo sprintf("%s: invalid email [%s]", $name, $email).PHP_EOL;
			$valid = false;
		}
		elseif (strpos($email,"kjvcourier") !== false) {
			echo sprintf("%s: uses kjvcourier email [%s]", $name, $email).PHP_EOL;
		}
	}
	//
	//	ShipTo Address
	//
	if (!array_key_exists("ShipAddr",$v)) {
		$valid = false;
		echo sprintf("%s: missing shipping address", $name).PHP_EOL;
	}
	else {
		if (!(
				array_key_exists("Line1",$v["ShipAddr"]) && 
				array_key_exists("City",$v["ShipAddr"]) && 
				array_key_exists("CountrySubDivisionCode",$v["ShipAddr"]) && 
				array_key_exists("PostalCode",$v["ShipAddr"]))) {
			$valid = false;
			echo sprintf("%s: incomplete shipping address", $name).PHP_EOL;
		}
		else {
			$prov = array_key_exists("CountrySubDivisionCode", $v["ShipAddr"]) ? $v["ShipAddr"]["CountrySubDivisionCode"] : "ON";
			if (strlen($prov) > 2) $prov =  substr($prov,0,2);
			$shipTo = array(
				"ownertype"=>"member",
				"line1"=>$v["ShipAddr"]["Line1"],
				"line2"=> array_key_exists("Line2",$v["ShipAddr"]) ? $v["ShipAddr"]["Line1"] : "",
				"city"=>$v["ShipAddr"]["City"],
				"postalcode"=>$v["ShipAddr"]["PostalCode"],
				"province_id"=>$c->fetchScalarTest("select id from provinces where province_code = '%s'", $prov),
				"country_id"=>$c->fetchScalarTest("select c.id from countries c, provinces p where p.province_code = '%s' and c.id = p.country_id", $prov),
				"addresstype"=>ADDRESS_PICKUP
			);
			if (array_key_exists("PrimaryPhone",$v) && array_key_exists("FreeFormNumber",$v["PrimaryPhone"])) {
				$shipTo["phone1"] = $v["PrimaryPhone"]["FreeFormNumber"];
			}
			if (array_key_exists("Fax",$v) && array_key_exists("FreeFormNumber",$v["Fax"])) {
				$shipTo["phone1"] = $v["Fax"]["FreeFormNumber"];
			}
			$lat = 0;
			$lng = 0;
			if (!$g_valid = $c->geocode($shipTo,$lat,$lng)) {
				$valid = false;
				echo sprintf("%s: unable to geocode pickup address for [%s, %s, %s]%s", $name, $shipTo["line1"], $shipTo["city"], $shipTo["postalcode"], PHP_EOL);
				//$c->logMessage(__FUNCTION__,sprintf("unable to geocode billing address for %s [%s %s]", $cust["company"], $address["line1"], $address["city"]), 1,true);
			}
			else {
				$shipTo["latitude"] = $lat;
				$shipTo["longitude"] = $lng;
			}
		}
	}
	if (!array_key_exists("BillAddr",$v)) {
		$valid = false;
		echo sprintf("%s: missing billing address", $name).PHP_EOL;
	}
	else {
		if (!(
					array_key_exists("Line1",$v["BillAddr"]) && 
					array_key_exists("City",$v["BillAddr"]) && 
					array_key_exists("CountrySubDivisionCode",$v["BillAddr"]) && 
					array_key_exists("PostalCode",$v["BillAddr"]))) {
			$valid = false;
			echo sprintf("%s: incomplete billing address", $name).PHP_EOL;
		}
		else {
			$prov = array_key_exists("CountrySubDivisionCode", $v["BillAddr"]) ? $v["BillAddr"]["CountrySubDivisionCode"] : "ON";
			if (strlen($prov) > 2) $prov =  substr($prov,0,2);
			$billTo = array(
				"ownertype"=>"member",
				"line1"=>$v["BillAddr"]["Line1"],
				"line2"=> array_key_exists("Line2",$v["BillAddr"]) ? $v["BillAddr"]["Line1"] : "",
				"city"=>$v["BillAddr"]["City"],
				"postalcode"=>$v["BillAddr"]["PostalCode"],
				"province_id"=>$c->fetchScalarTest("select id from provinces where province_code = '%s'", $prov),
				"country_id"=>$c->fetchScalarTest("select c.id from countries c, provinces p where p.province_code = '%s' and c.id = p.country_id", $prov),
				"addresstype"=>ADDRESS_BILLING
			);
			if (array_key_exists("PrimaryPhone",$v) && array_key_exists("FreeFormNumber",$v["PrimaryPhone"])) {
				$billTo["phone1"] = $v["PrimaryPhone"]["FreeFormNumber"];
			}
			if (array_key_exists("Fax",$v) && array_key_exists("FreeFormNumber",$v["Fax"])) {
				$billTo["phone1"] = $v["Fax"]["FreeFormNumber"];
			}
			if (!$g_valid = $c->geocode($billTo,$lat,$lng)) {
				$valid = false;
				echo sprintf("%s: unable to geocode billing address for [%s, %s, %s]%s", $name, $billTo["line1"], $billTo["city"], $billTo["postalcode"], PHP_EOL);
				//$c->logMessage(__FUNCTION__,sprintf("unable to geocode billing address for %s [%s %s]", $cust["company"], $address["line1"], $address["city"]), 1,true);
			}
			else {
				$billTo["latitude"] = $lat;
				$billTo["longitude"] = $lng;
			}
		}
	}
	if (!$valid) continue;
	//continue;
	$cust = array(
		"company" => $name,
		"custom_qb_id"=>$v["Id"],
		"created"=>date(DATE_ATOM),
		"enabled"=>1,
		"deleted"=>0,
		"custom_on_account"=>1,
		"email" => strtolower(array_key_exists("PrimaryEmailAddr",$v) ? $v["PrimaryEmailAddr"]["Address"] : str_replace(" ","_",$name)."@kjvcourier.com")
	);
	$c->beginTransaction();
	$c_stmt = $c->prepare(sprintf("insert into members(%s) values(?%s)", implode(", ",array_keys($cust)), str_repeat(", ?", count($cust)-1)));
	$c_stmt->bindParams(array_merge(array(str_repeat("s",count($cust))),array_values($cust)));
	$valid = $c_stmt->execute();
	if ($valid) {
		$owner = $c->insertId();
		$shipTo["ownerid"] = $owner;
		$a_stmt = $c->prepare(sprintf("insert into addresses(%s) values(?%s)", implode(", ",array_keys($shipTo)), str_repeat(", ?", count($shipTo)-1)));
		$a_stmt->bindParams(array_merge(array(str_repeat("s",count($shipTo))),array_values($shipTo)));
		$valid &= $a_stmt->execute();

		$billTo["ownerid"] = $owner;
		$a_stmt = $c->prepare(sprintf("insert into addresses(%s) values(?%s)", implode(", ",array_keys($billTo)), str_repeat(", ?", count($billTo)-1)));
		$a_stmt->bindParams(array_merge(array(str_repeat("s",count($billTo ))),array_values($billTo )));
		$valid &= $a_stmt->execute();

		$f_stmt = $c->prepare(sprintf("insert into members_by_folder( member_id, folder_id ) values( ?, ? )"));
		$f_stmt->bindParams(array("ii", $owner, 22));
		$valid &= $f_stmt->execute();
	}
	if ($valid) {
		$c->commitTransaction();
		//$c->rollbackTransaction();
	}
	else {
		$c->rollbackTransaction();
	}
	echo $k.":".(array_key_exists("CompanyName",$v) ? $v["CompanyName"] : $v["DisplayName"]).PHP_EOL;
}

?>