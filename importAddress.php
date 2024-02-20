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

/**
 * Handles common import functionality
 */
class importCheck extends Common {

	protected $m_module = array();
	protected $config;
	protected $m_validateOnly;
	protected $validFields = array();
	protected $m_sheetData = array();
	protected $fe;
	private $_ownerId = 1573;
	private $_city = "Toronto";

    /**
     * @param $init
     * @param $debugLog
     * @throws Exception
     */
    function __construct($init = false, $debugLog = '') {
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

    /**
     * Destructor
     */
    function __destruct() {
		
	}

    /**
     * @param $fn
     * @return false
     * @throws phpmailerException
     */
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
		}
		catch(Exception $err) {
			$this->logMessage(__FUNCTION__, sprintf("error found [%s]", print_r($err,true)),1);
			echo $err->getMessage();
			return false;
		}
		return $valid;
	}

    /**
     * @param $data
     * @return void
     */
    function setSheetData($data) {
		$this->m_sheetData = $data;
	}

    /**
     * @return array
     */
    function getSheetData() {
		return $this->m_sheetData;
	}

    /**
     * @return void
     * @throws phpmailerException
     */
    function doImport() {
		$status = true;
		$data = $this->getSheetData();
		$flds = $this->validFields;
		$flds = array("addresstype"=>ADDRESS_PICKUP, "ownertype"=>"member", "ownerid"=> $this->_ownerId, "line1"=>"", "company"=>"", "city"=>"Toronto", "firstname"=>"", "lastname"=>"", "postalcode"=>"", "phone1"=>"", "email"=>"", "latitude"=>0.0, "longitude"=>0.0, "address_book"=>1,"province_id"=>1, "country_id"=>1,"residential"=>0);
		$stmt = $this->prepare(sprintf("insert into addresses(%s) values(?%s)", implode(", ",array_keys($flds)), str_repeat(", ?", count($flds)-1)));
		foreach($data as $k=>$v) {
			if ($k==1) continue 1;
			$flds["line1"] = $v["D"];
			$flds["postalcode"] = $v["E"];
			$flds["company"] = $v["A"];
			$flds["firstname"] = strlen($v["N"]) > 0 ? $v["N"] : "-";
			$flds["lastname"] = strlen($v["M"]) > 0 ? $v["M"] : "-";
			$flds["phone1"] = strlen($v["P"]) > 0 ? $v["P"] : "000-0000";
			$flds["email"] = strlen($v["O"]) > 0 ? $v["O"] : " ";
			$lat = 0;
			$long = 0;
			if (!$this->geoCode($flds, $flds["latitude"], $flds["longitude"])) {
				$this->logMessage(__FUNCTION__,sprintf("failed validation on [%s]", print_r($flds,true)),1);
			}
			else {
				$stmt->bindParams(array_merge(array(str_repeat("s",count($flds))),array_values($flds)));
				$this->beginTransaction();
				if ($stmt->execute())
					$this->commitTransaction();
				else {
					$this->rollbackTransaction();
					$this->logMessage(__FUNCTION__,sprintf("Insert failed on [%s]", print_r($flds,true)),1);
				}
			}
		}
	}
}

session_start();
ob_start();
date_default_timezone_set(TZ);
setlocale(LC_MONETARY,CURRENCY);
set_time_limit(60*20);
$chk = new ImportCheck(true,sprintf("addresses_%s", date("Y-m-d")));
$chk->validate('WCH2021a.xlsx');
error_reporting(E_ALL & ~E_STRICT);
$chk->doImport();
ob_end_flush();

?>