<?php

require_once("config.php");
require_once(ADMIN."config.php");
require_once(ADMIN."classes/globals.php");
require_once(ADMIN."classes/PHPMailer/PHPMailerAutoload.php");
require_once(ADMIN."classes/common.php");
require_once(ADMIN."classes/Forms.php");
require_once(ADMIN."classes/HtmlElement.php");
require_once(ADMIN."classes/mptt.php");
require_once(ADMIN."classes/Snoopy.php");
require_once(ADMIN."classes/Google/autoload.php");
require_once(ADMIN."classes/phpoffice/vendor/autoload.php");
require_once(ADMIN."classes/KJV.php");
require_once(ADMIN."frontend/Frontend.php");
require_once(ADMIN."frontend/modules/custom.php");
require_once(ADMIN."frontend/modules/product.php");
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class importCheck extends Common {

	protected $m_module = array();
	protected $config;
	protected $m_validateOnly;
	protected $validFields = array();
	protected $m_handling_fee = 0;
	protected $m_fieldList = array(
		'pickup_first_name'=>array("required"=>true,"mapTo"=>""),
		'pickup_last_name'=>array("required"=>true,"mapTo"=>""),
		'pickup_company'=>array("required"=>false,"mapTo"=>"","checkForNull"=>true),
		'pickup_address_id'=>array("required"=>false,"mapTo"=>""),
		'pickup_line_1'=>array("required"=>true,"mapTo"=>""),
		'pickup_line_2'=>array("required"=>false,"mapTo"=>"","checkForNull"=>true),
		'pickup_city'=>array("required"=>true,"mapTo"=>""),
		'pickup_province'=>array("required"=>true,"mapTo"=>"","default"=>"Ontario"),
		'pickup_country'=>array("required"=>false,"mapTo"=>"","default"=>"Canada"),
		'pickup_postal_code'=>array("required"=>true,"mapTo"=>""),
		'pickup_email'=>array("required"=>false,"mapTo"=>""),
		'pickup_phone'=>array("required"=>false,"mapTo"=>""),
		'pickup_residential'=>array("required"=>false,"mapTo"=>"","default"=>0),
		'delivery_first_name'=>array("required"=>true,"mapTo"=>""),
		'delivery_last_name'=>array("required"=>true,"mapTo"=>""),
		'delivery_company'=>array("required"=>false,"mapTo"=>"","checkForNull"=>true),
		'delivery_address_id'=>array("required"=>false,"mapTo"=>""),
		'delivery_line_1'=>array("required"=>true,"mapTo"=>""),
		'delivery_line_2'=>array("required"=>false,"mapTo"=>"","checkForNull"=>true),
		'delivery_city'=>array("required"=>true,"mapTo"=>""),
		'delivery_province'=>array("required"=>true,"mapTo"=>"","default"=>"Ontario"),
		'delivery_country'=>array("required"=>false,"mapTo"=>"","default"=>"Canada"),
		'delivery_postal_code'=>array("required"=>true,"mapTo"=>""),
		'delivery_email'=>array("required"=>false,"mapTo"=>""),
		'delivery_phone'=>array("required"=>false,"mapTo"=>""),
		'delivery_residential'=>array("required"=>false,"mapTo"=>"","default"=>0),
		'pickup_date_time'=>array("required"=>true,"mapTo"=>""),
		'quantity'=>array("required"=>true,"mapTo"=>""),
		'service'=>array("required"=>true,"mapTo"=>""),
		'packaging'=>array("required"=>true,"mapTo"=>""),
		'weight'=>array("required"=>true,"mapTo"=>""),
		'weight_units'=>array("required"=>true,"mapTo"=>"","default"=>"LB"),
		'height'=>array("required"=>true,"mapTo"=>""),
		'width'=>array("required"=>true,"mapTo"=>""),
		'depth'=>array("required"=>true,"mapTo"=>""),
		'dimension_units'=>array("required"=>true,"mapTo"=>"","default"=>"IN"),
		'pickup_instructions'=>array("required"=>false,"mapTo"=>""),
		'delivery_instructions'=>array("required"=>false,"mapTo"=>""),
		'reference_number'=>array("required"=>false,"mapTo"=>""),
		'insurance'=>array("required"=>false,"mapTo"=>"","default"=>0),
		'declared_value'=>array("required"=>false,"mapTo"=>"","default"=>0),
		'pickup_driver'=>array('required'=>false,'mapTo'=>'','default'=>''),
		'delivery_driver'=>array('required'=>false,'mapTo'=>'','default'=>'')
	);
	protected $addressCheck = array(
		"firstname"=>array("type"=>"string"),
		"lastname"=>array("type"=>"string"),
		"line1"=>array("type"=>"string"),
		"line2"=>array("type"=>"string"),
		"city"=>array("type"=>"string"),
		"postalcode"=>array("type"=>"string"),
		"phone1"=>array("type"=>"string"),
		"phone2"=>array("type"=>"string"),
		"fax"=>array("type"=>"string"),
		"email"=>array("type"=>"string")
	);
	protected $m_privateid = 0;
	protected $m_importId = 0;
	protected $m_sheetData = array();
	protected $fe;

	function added($orderId) {
	}

	function setImportId( $id ) {
		$this->m_importId = $id;
	}

	function getImportId() {
		return $this->m_importId;
	}

	function setHandlingFee( $id ) {
		$this->m_handling_fee = $id;
	}

	function getHandlingFee( ) {
		return $this->m_handling_fee;
	}

	function validateOnly( $value ) {
		$this->m_validateOnly = $value;
	}

	function isValidation() {
		return $this->m_validateOnly;
	}

	function logUpdate( $row, $col, $msg, $level ) {
		$logMsg = sprintf("Row: %d, Col: %s %s", $row, $col, $msg);
		$values = array("row_id"=>$row, "col_id"=>$col, "import_id"=>$this->m_importId, "message"=>$logMsg, "status"=>$level);
		$stmt = $this->prepare(sprintf("insert into order_import_messages(%s) values(?%s)", implode(", ",array_keys($values)), str_repeat(", ?", count($values)-1)));
		$stmt->bindParams(array_merge(array(str_repeat("s",count($values))),array_values($values)));
		$stmt->execute();
		if ($level==1)
			$this->execute(sprintf("update order_import set error_count = error_count + 1 where id = %d", $this->m_importId));
	}

	function __construct($init = false,$debugLog = '') {
		if ($init) {
			$this->m_privateid = random_int(0,99);
			$GLOBALS['globals'] = new Globals(DEBUG,false,$debugLog);
			$GLOBALS['globals']->setConfig(new custom(0));
			$this->config = $GLOBALS['globals']->getConfig();
			$this->fe = new Frontend();
		}
		else {
			$this->m_privateid = '';
		}
	}
	
	function __destruct() {
		
	}

	function validate($fn) {
		$this->logMessage(__FUNCTION__, sprintf("checking file [%s]", $fn),1);
		$valid = false;
		$rdr  = IOFactory::createReader("Xlsx");
		$this->logMessage(__FUNCTION__, sprintf("rdr 1 [%s]", print_r($rdr,true)),4);
		$rdr->setReadDataOnly(true);
		$this->logMessage(__FUNCTION__, sprintf("rdr 2 [%s]", print_r($rdr,true)),4);
		$this->validFields = $this->m_fieldList;
		try {
			$sheet = $rdr->load($fn);
			$this->setsheetData($sheet->getActiveSheet()->toArray(null, true, true, true));
			$hdr = $this->getSheetData()[1];
			$this->logMessage(__FUNCTION__, sprintf("header [%s]", print_r($hdr,true)),1);
			$valid = true;
			foreach($hdr as $k=>$v) {
				if (array_key_exists($v,$this->validFields)) {
					$this->validFields[$v]["mapTo"] = $k;
				}
				else {
					$valid = false;
					$this->logUpdate( 1, $k, sprintf("Unknown Field [%s]", $v), 1);
				}
			}
			foreach($this->validFields as $k=>$v) {
				if ($v["required"] && $v["mapTo"] == "") {
					$this->logUpdate( 1, $k, sprintf("Missing Required Field [%s]", $k), 1);
					$valid = false;
				}
			}
			$this->logMessage(__FUNCTION__, sprintf("field list [%s]", print_r($this->validFields,true)),3);
		}
		catch(Exception $err) {
			$this->logMessage(__FUNCTION__, sprintf("error found [%s]", print_r($err,true)),1);
			echo $err->getMessage();
			return false;
		}
		return $valid;
	}

	function setSheetData($data) {
		$this->m_sheetData = $data;
	}

	function getSheetData() {
		return $this->m_sheetData;
	}

	function getFieldData($name, $data, $chkForNull = false) {
		$value = array_key_exists("default",$name) ? $name["default"] : "";
		if ($name["mapTo"] != "") {
			$value = $data[$name["mapTo"]];
		}
		if ($chkForNull && is_null($value)) $value = "";
		return $value;
	}

	function checkCountry($name) {
		return $this->fetchScalar(sprintf('select id from countries where (country = "%s" or country_code = "%1$s") and deleted = 0', $name));
	}

	function checkProvince($c_id, $name) {
		return $this->fetchScalar(sprintf('select id from provinces where country_id = %d and (province = "%2$s" or province_code = "%2$s")', $c_id, $name));
	}

	function buildAddress($type, $row, $v, $flds, &$valid) {
		$addr = array();
		if ($flds[$type."_address_id"]["mapTo"] != "" && $v[$flds[$type."_address_id"]["mapTo"]] > 0) {
			if (!($addr = $this->fetchSingle(sprintf("select * from addresses where id = %d and ownertype = 'member' and ownerid = %d", $v[$flds[$type."_address_id"]["mapTo"]], $this->getUserInfo("id"))))) {
				$valid = false;
				$this->logUpdate($k,$flds[$type."_address_id"]["mapTo"],sprintf("Invalid address id [%s]", $v[$flds[$type."_address_id"]["mapTo"]]), 1);
			}
		}
		else {
			$addr = array(
				"line1"=>$this->getFieldData($flds[$type."_line_1"],$v),
				"line2"=>$this->getFieldData($flds[$type."_line_2"],$v, array_key_exists("checkForNull",$flds[$type."_line_2"]) ? $flds[$type."_line_2"]["checkForNull"] : false),
				"city"=>$this->getFieldData($flds[$type."_city"],$v),
				"postalcode"=>$this->getFieldData($flds[$type."_postal_code"],$v),
				"phone1"=>$this->getFieldData($flds[$type."_phone"],$v),
				"email"=>$this->getFieldData($flds[$type."_email"],$v),
				"firstname"=>$this->getFieldData($flds[$type."_first_name"],$v),
				"lastname"=>$this->getFieldData($flds[$type."_last_name"],$v),
				"company"=>$this->getFieldData($flds[$type."_company"],$v, array_key_exists("checkForNull",$flds[$type."_company"]) ? $flds[$type."_company"]["checkForNull"] : false),
				"residential"=>$this->getFieldData($flds[$type."_residential"],$v),
				"ownerid"=>$this->getUserInfo("id"),
				"addressbook_id"=>0,
				"id"=>0
			);
			if (!$c_id = $this->checkCountry($this->getFieldData($flds[$type."_country"],$v))) {
				$valid = false;
				$this->logUpdate($k, $type."_country", sprintf("Invalid country [%s]", $this->getFieldData($flds[$type."_country"],$v)), 1);
			}
			else {
				$addr["country_id"] = $c_id;
				$addr["country_code"] = $this->fetchScalar(sprintf("select country_code from countries where id = %d", $c_id));
				$addr["countryCode"] = $addr["country_code"];
				if (!$p_id = $this->checkProvince($c_id, $this->getFieldData($flds[$type."_province"],$v))) {
					$valid = false;
					$this->logUpdate($row, $type."_province", sprintf("Invalid province [%s]", $this->getFieldData($flds[$type."_province"],$v)), 1);
				}
				else {
					$addr["province_id"] = $p_id;
					$addr["province_code"] = $this->fetchScalar(sprintf("select province_code from provinces where id = %d", $p_id));
					$addr["provinceCode"] = $addr["province_code"];
				}
			}
			$lat = 0;
			$lng = 0;
$form = new Forms();
$form->buildForm(array("postalcode"=>array("type"=>"textfield","required"=>true,"validation"=>"postalcode")));
$form->addData($addr);
$form->setHTML("%%errorMessage%%");
if (!($valid = $form->validate())) {
	$tmp = $form->getFormErrors();
	$this->logMessage(__FUNCTION__,sprintf("^^^ errors [%s]", print_r($tmp,true)),1);
	$this->logUpdate($row, $type."_address", sprintf("Address did not validate [%s]", implode(", ",$tmp)),1);
}
			if ($valid) {
				if (!$this->geocode($addr, $lat, $lng)) {
					$this->logUpdate($row, $type."_address", sprintf("Could not parse address [%s %s %s]", $addr["line1"], $addr["city"], $addr["postalcode"]), 1);
					$valid = false;
				}
				else {
					$addr["latitude"] = $lat;
					$addr["longitude"] = $lng;
				}
			}
		}
		return $addr;
	}

	function getWeightCode( $row, $data, $flds, &$valid ) {
		$cd = $this->getFieldData($flds["weight_units"], $data);
		if (!$c_id = $this->fetchScalar(sprintf("select id from code_lookups where type='weights' and code = '%s'", $cd))) {
			$valid = false;
			$this->logUpdate($row, "weight_units", sprintf("Invalid Weight Code [%s]", $cd),1);
		}
		return $c_id;
	}

	function getDimensionCode( $row, $data, $flds, &$valid ) {
		$cd = $this->getFieldData($flds["dimension_units"], $data);
		if (!$c_id = $this->fetchScalar(sprintf("select id from code_lookups where type='dimensions' and code = '%s'", $cd))) {
			$valid = false;
			$this->logUpdate($row, "dimension_units", sprintf("Invalid Dimensions Code [%s]", $cd),1);
		}
		return $c_id;
	}

	function getPackaging( $row, $data, $flds, &$valid ) {
		if (!$grp = $this->fetchSingle(sprintf("select p.* 
		from product p, product_by_folder pbf, members_folders mf, members_by_folder mbf 
		where mbf.member_id = %d and mf.id = mbf.folder_id and pbf.folder_id = mf.custom_package_types and p.id = pbf.product_id and p.name = '%s' and p.deleted = 0", $this->getUserInfo("id"), $data[$flds["packaging"]["mapTo"]]))) {
			$valid = false;
			$this->logUpdate( $row, "packaging", sprintf("Invalid packaging code [%s]", $data[$flds["packaging"]["mapTo"]]), 1);
			return 0;
		}
		return $grp["id"];
	}

	function getServiceType( $row, $data, $flds, &$valid ) {
		$junction = $this->fetchSingle(sprintf("select * from members_by_folder where member_id = %d limit 1", $this->getUserInfo("id")));
		if ($mbr_prod = $this->fetchSingle(sprintf("select cpo.* from product p, custom_member_product_options cpo where cpo.member_id = %d and cpo.isgroup = 0 and p.id = cpo.product_id and p.name = '%s'", $junction["id"], $data[$flds["service"]["mapTo"]]))) {
			$type = $mbr_prod["id"];
		}
		else if ($grp_prod = $this->fetchSingle(sprintf("select cpo.* from product p, custom_member_product_options cpo where cpo.member_id = %d and cpo.isgroup = 1 and p.id = cpo.product_id and p.name = '%s'", $junction["folder_id"], $data[$flds["service"]["mapTo"]]))) {
			$type = $grp_prod["id"];
		}
		else {
			$valid = false;
			$this->logUpdate( $row, "service", sprintf("Invalid Service Type [%s]", $data[$flds["service"]["mapTo"]]),1);
			return 0;
		}
		return $type;
	}

	function doImport() {
		$status = true;
		$data = $this->getSheetData();
		$flds = $this->validFields;
		foreach($data as $k=>$v) {
			if ($k==1) continue 1;
			if ($chk = $this->fetchScalar(sprintf("select abort from order_import where id = %d", $this->getImportId()))) {
				$this->logUpdate($k,"","Processing aborted",1);
				return false;
			}
			$valid = true;
			$this->logMessage(__FUNCTION__, sprintf("row [%s] data [%s]", $k, print_r($v,true)),1);
			foreach($flds as $sk=>$sv) {
				if ($sv["required"]) {
					if ($v[$sv["mapTo"]] == "" || is_null($v[$sv["mapTo"]])) {
						$status = false;
						$this->logUpdate($k,$sk,sprintf("Required Data missing [%s]", $v[$sk["mapTo"]]),1);
					}
				}
			}
			if (!$status) continue 1;
			$cart = Ecom::initCart();
			//
			//	Build & Validate p/u address
			//
			$cart["addresses"] = array();
			$valid = true;
			$cart["addresses"]["pickup"] = $this->buildAddress("pickup", $k, $v, $flds, $valid);
			$cart["addresses"]["shipping"] = $this->buildAddress("delivery", $k, $v, $flds, $valid);
			$pu = max(strtotime("now"),strtotime($v[$flds["pickup_date_time"]["mapTo"]]));
			$_POST = array(
				"dry_ice_weight" => 0,
				"custom_placed_by" => "automated load",
				"custom_pickup_email" => "",
				"custom_email_confirmation" => "",
				"pickupInstructions" => "",
				"deliveryInstructions" => "",
				"customs_declaration" => "",
				"custom_declared_value" => 0.00,
				"custom_reference_number" => "",
				"pickup_datetime" => date("m/d/Y",$pu),
				"pickup_datetime_hh" => date("h",$pu),
				"pickup_datetime_mm" => date("i",$pu),
				"pickup_datetime_ampm" => date("A",$pu),
				"pickup_datetime_ss" => "00",
				"selectService" => 1,
				"custom_override_price" => 0.00,
				"custom_recurring_pu_driver" => "",
				"custom_recurring_del_driver" => "",
				"t_id" => 11	
			);
			$_POST["serviceType"] = $this->getServiceType( $k, $v, $flds, $valid );	//1187; // junction id & product id lookup into custom_member_product_options
			$_POST["custom_weight_code"] = $this->getWeightCode($k, $v, $flds, $valid);
			$_POST["custom_dimension_code"] = $this->getDimensionCode($k, $v, $flds, $valid);
			$_POST["custom_reference_number"] = $this->getFieldData($flds["reference_number"],$v);
			$_POST["custom_insurance"] = $this->getFieldData($flds["insurance"],$v);
			$_POST["custom_declared_value"] = $this->getFieldData($flds["declared_value"],$v);
			$_POST["prod"] = array(
				1=>array(
					"product_id"=>$this->getPackaging($k, $v, $flds, $valid),
					"custom_weight"=>$v[$flds["weight"]["mapTo"]],
					"quantity"=>$v[$flds["quantity"]["mapTo"]],
					"dimensions"=>array(
						1=>array(
							"quantity"=>$v[$flds["quantity"]["mapTo"]],
							"weight"=>$v[$flds["weight"]["mapTo"]],
							"depth"=>$v[$flds["depth"]["mapTo"]],
							"width"=>$v[$flds["width"]["mapTo"]],
							"height"=>$v[$flds["height"]["mapTo"]]
						)
					)
				)
			);

			$pu_driver = $this->getFieldData($flds["pickup_driver"],$v);
			$del_driver = $this->getFieldData($flds["delivery_driver"],$v);
			if (strlen($pu_driver) > 0) {
				if ($pu_id = $this->fetchScalar(sprintf("select d.id from drivers d, members m where d.deleted = 0 and  d.enabled = 1 and d.member_id = m.id and m.enabled and m.deleted = false and m.company = '%s'", $pu_driver))) {
					$_POST["custom_recurring_pu_driver"] = $pu_id;
				}
				else {
					$this->logUpdate($k, "pickup_driver", sprintf("Invalid driver id [%s]", $pu_driver),1);
					$valid = false;
				}
			}
			if (strlen($del_driver) > 0) {
				if ($del_id = $this->fetchScalar(sprintf("select d.id from drivers d, members m where d.deleted = 0 and  d.enabled = 1 and d.member_id = m.id and m.enabled and m.deleted = false and m.company = '%s'", $del_driver))) {
					$_POST["custom_recurring_del_driver"] = $del_id;
				}
				else {
					$this->logUpdate($k, "delivery_driver", sprintf("Invalid driver id [%s]", $del_driver),1);
					$valid = false;
				}
			}
			$isFedex = false;
			if ($valid) {

				//
				//	Fedex vs KJV delivery check
				//
				$allowedZones = $this->fetchScalarAll(sprintf("select zone_id from zones_by_folder z, members m where m.id = %d and z.folder_id = m.custom_zones", $this->getUserInfo("id")));
							
				$fromZone = $this->fetchSingle(sprintf("select zf.*, f.downtown from zone_fsa zf, fsa f where f.fsa='%s' and zf.fsa_id = f.id and zone_id in (%s)",
									strtoupper(substr($cart["addresses"]["pickup"]["postalcode"],0,3)), is_array($allowedZones) ? implode(", ", $allowedZones) : 0));
				$toZone = $this->fetchSingle(sprintf("select zf.*, f.downtown from zone_fsa zf, fsa f where f.fsa='%s' and zf.fsa_id = f.id and zone_id in (%s)",
									strtoupper(substr($cart["addresses"]["shipping"]["postalcode"],0,3)), is_array($allowedZones) ? implode(", ", $allowedZones) : 0));
				//
				//	inzone = kjv delivery or fedex [different rating code]
				//
				$_SESSION["cart"]["header"]["inzone"] = 1;
				$prod = $this->fetchSingle(sprintf("select p.* from product p, custom_member_product_options cpo where cpo.id = %d and p.id = cpo.product_id", $_POST["serviceType"]));
				if (!(is_array($fromZone) && is_array($toZone))) {
					$this->logMessage(__FUNCTION__, sprintf("^^^ start fedex test"),1);
					if (!$prod["is_fedex"]) {
						$this->logUpdate($k,"",sprintf("Out of Zone delivery with an In Zone service [%s] Pickup [%s] Delivery [%s]", $v[$flds["service"]["mapTo"]], $cart["addresses"]["pickup"]["city"], $cart["addresses"]["shipping"]["city"]),1);
						$valid = false;
					}
					else {
						$this->logMessage(__FUNCTION__,sprintf("^^^ start fedex quotes"),1);
						$isFedex = true;
						$_SESSION["cart"]["header"]["inzone"] = 0;
						$_POST["wt"] = $this->fetchSingle(sprintf("select * from code_lookups where id = %d",$_POST["custom_weight_code"]));
						$_POST["sz"] = $this->fetchSingle(sprintf("select * from code_lookups where id = %d",$_POST["custom_dimension_code"]));
						$tmp = $GLOBALS['globals']->getConfig()->getFedex($cart,$_POST);
						$this->logMessage(__FUNCTION__,sprintf("^^^ pre cart [%s] post cart [%s]", print_r($cart,true), print_r($tmp,true)),1);
						$cart = $tmp;
						if (!(count($cart) > 0 && $this->checkArray("custom:rates",$cart))) {
							$this->logUpdate($k,"",sprintf("Could not get a rate [%s]", $v[$flds["service"]["mapTo"]]), 1);
							$valid = false;
						}
					}
				}
				else {
					if ($prod["is_fedex"]) {
						$this->logUpdate($k,"",sprintf("In Zone delivery with a Out of Zone service [%s] Pickup [%s] Delivery [%s]", $v[$flds["service"]["mapTo"]], $cart["addresses"]["pickup"]["city"], $cart["addresses"]["shipping"]["city"]),1);
						$valid = false;
					}
				}
			}
			if ($valid) {
				$cart["header"]["custom_dimension_code"] = $_POST["custom_dimension_code"];
				$cart["header"]["custom_weight_code"] = $_POST["custom_weight_code"];
				$cart["header"]["custom_reference_number"] = $_POST["custom_reference_number"];
				$cart["header"]["pickupInstructions"] = $_POST["pickupInstructions"];
				$cart["header"]["deliveryInstructions"] = $_POST["deliveryInstructions"];
				$cart["header"]["custom_insurance"] = $_POST["custom_insurance"];
				$cart["header"]["custom_declared_value"] = $_POST["custom_declared_value"];
				$cart["header"]["pickup_datetime"] = date("Y-m-d H:i",$pu);
				$extras = array();
				if (($fee = $this->getHandlingFee()) > 0) {
					$extras = array(0=>$fee);
				}
				$_SESSION["cart"] = $cart;
				$_SESSION["quote"] = $_POST;
				$this->logMessage(__FUNCTION__, sprintf("cart [%s] post [%s]", print_r($cart,true), print_r($_POST,true)),1);
				$_REQUEST = array(
					"ajax" => "render",
					"t_id" => 37,
					"KJVService" => 1,
					"custom_weight_code" => $_POST["custom_weight_code"],
					"custom_dimension_code" => $_POST["custom_dimension_code"],
					"serviceType" => $_POST["serviceType"]
				);
				$_POST = $_REQUEST;
				$this->logMessage(__FUNCTION__,sprintf("^^^ start getPrice"),1);
				$kjv = new KJV();

				$quote = $kjv->getPrice( $_SESSION["quote"]["prod"], $extras, $cart["addresses"]["pickup"], $cart["addresses"]["shipping"], $_POST["serviceType"], $_POST["custom_weight_code"], $_POST["custom_dimension_code"] );
				$this->logmessage(__FUNCTION__,sprintf("^^^ quote [%s] session [%s]", print_r($quote,true), print_r($_SESSION,true)),1);
				$p_obj = new product(0);
				$this->beginTransaction();
				unset($_SESSION["cart"]["addresses"]["pickup"]["country_code"]);
				unset($_SESSION["cart"]["addresses"]["pickup"]["countryCode"]);
				unset($_SESSION["cart"]["addresses"]["pickup"]["province_code"]);
				unset($_SESSION["cart"]["addresses"]["pickup"]["provinceCode"]);
				unset($_SESSION["cart"]["addresses"]["shipping"]["country_code"]);
				unset($_SESSION["cart"]["addresses"]["shipping"]["countryCode"]);
				unset($_SESSION["cart"]["addresses"]["shipping"]["province_code"]);
				unset($_SESSION["cart"]["addresses"]["shipping"]["provinceCode"]);
				foreach($this->addressCheck as $sk=>$sv) {
					if (array_key_exists($sk,$_SESSION["cart"]["addresses"]["shipping"]) && is_null($_SESSION["cart"]["addresses"]["shipping"][$sk]))
						$_SESSION["cart"]["addresses"]["shipping"][$sk] = "";
					if (array_key_exists($sk,$_SESSION["cart"]["addresses"]["pickup"]) && is_null($_SESSION["cart"]["addresses"]["pickup"][$sk]))
						$_SESSION["cart"]["addresses"]["pickup"][$sk] = "";
				}
				$_SESSION["cart"]["addresses"]["shipping"]["addresstype"] = ADDRESS_DELIVERY;
				$_SESSION["cart"]["addresses"]["pickup"]["addresstype"] = ADDRESS_PICKUP;
				$this->logMessage(__FUNCTION__,sprintf("^^^ cart before createOrder [%s]", print_r($_SESSION["cart"],true)),1);
				$this->logMessage(__FUNCTION__,sprintf("line2 [%s] = null [%s] = '' [%s]", $_SESSION["cart"]["addresses"]["shipping"]["line2"], is_null($_SESSION["cart"]["addresses"]["shipping"]["line2"]), $_SESSION["cart"]["addresses"]["shipping"]["line2"] == ""),1);
				if (is_null($_SESSION["cart"]["addresses"]["shipping"]["line2"])) $_SESSION["cart"]["addresses"]["shipping"]["line2"] = "";
				if (is_null($_SESSION["cart"]["addresses"]["pickup"]["line2"])) $_SESSION["cart"]["addresses"]["pickup"]["line2"] = "";
				if (!$this->m_validateOnly) {
					if ($valid = $p_obj->createOrder($_SESSION["cart"],$orderid)) {
						$ord = $this->prepare(sprintf("insert into order_import_details(import_id, row_id, order_id) values(?, ?, ?)"));
						$ord->bindParams(array("sss",$this->m_importId, $k, $orderid));
						$ord->execute();
						$this->logUpdate($k,"",sprintf("Order #%d", $orderid),3);
						$this->execute(sprintf("update order_import set order_count = order_count + 1 where id = %d", $this->m_importId));
						$GLOBALS['globals']->getConfig()->postSaleProcessing($orderid,$valid,new Common(false));
						$this->execute(sprintf("update orders set order_status = %d where id = %d", STATUS_PROCESSING, $orderid));
						$this->commitTransaction();
					}
					else {
						//$this->logMessage(__FUNCTION__,sprintf("^^^messages [%s]", print_r($GLOBALS['globals']->getMessages(),true)),1);
						//$this->logMessage(__FUNCTION__,sprintf("^^^errors [%s]", print_r($GLOBALS['globals']->getErrors(),true)),1);
						$this->rollbackTransaction();
						$this->logUpdate($k,"",sprintf("An error occurred: %s", implode("<br/>", $GLOBALS['globals']->getErrors())),1);
					}
				}
				else {
					$this->rollbackTransaction();
					$this->execute(sprintf("update order_import set order_count = order_count + 1 where id = %d", $this->m_importId));
				}
			}
		}
		return $status;
	}
}

session_start();
ob_start();
date_default_timezone_set(TZ);
setlocale(LC_MONETARY,CURRENCY);
set_time_limit(60*20);
$saved_session = $_SESSION;
$chk = new ImportCheck(true,sprintf("import_%s", date("Y-m-d")));
$recs = $chk->fetchAll(sprintf("select * from order_import where processed = 0"));
foreach($recs as $k=>$v) {
	$fn = sprintf("/tmp/%s", $v["filename"]);
	$chk->setImportId($v["id"]);
	$chk->setHandlingFee($v["handling_fee"]);
	$chk->validateOnly( $v["validate_only"] );
	echo sprintf("Start processing %s @ %s%s",$v["filename"], date("Y-m-d h:i:s"), PHP_EOL);
	$chk->execute(sprintf("delete from order_import_messages where import_id = %d", $v["id"]));
	$chk->execute(sprintf("delete from order_import_details where import_id = %d", $v["id"]));
	$chk->execute(sprintf("update order_import set order_count = 0, error_count = 0 where id = %d", $v["id"]));
	if ($fh = fopen($fn, "w")) {
		fwrite($fh, $v["attachment"]);
		fclose($fh);
		if ($chk->validate($fn)) {
			$chk->logMeIn("","",false,$v["member_id"]);
			$status = $chk->doImport();
			$chk->logMessage(__FUNCTION__,sprintf("///done/// status [%s] validateOnly [%s]", $status, $chk->isValidation()),1);
			if ($status && !$chk->isValidation()) $chk->execute(sprintf("update order_import set processed=1 where id = %d", $v["id"]));
		}
		unlink($fn);
		echo sprintf("End processing %s @ %s%s",$v["filename"], date("Y-m-d h:i:s"), PHP_EOL);
	}
	else echo "failed to create file ".$v["filename"];
}
$_SESSION = $saved_session;
ob_end_flush();

?>