<?php

session_start();
ob_start();
$st = explode(' ',microtime());
error_reporting(E_ALL & !E_STRICT);
set_time_limit(60*20);

require_once("config.php");
require_once(ADMIN."config.php");
require_once(ADMIN."classes/globals.php");
require_once(ADMIN."classes/mailer.php");
require_once(ADMIN."classes/smtp.php");
require_once(ADMIN."classes/common.php");
require_once(ADMIN."classes/Forms.php");
require_once(ADMIN."classes/HtmlElement.php");
require_once(ADMIN."frontend/Frontend.php");
//require_once(ADMIN."classes/tcpdf/tcpdf.php");//updating to latest version using php packagemanger ,so it will load automatically
require_once(ADMIN."frontend/modules/custom.php");
date_default_timezone_set(TZ);
setlocale(LC_MONETARY,CURRENCY);
error_reporting(E_ALL & !E_STRICT);

echo "start";

$c = new Common(true,false,sprintf("IMG-%s",date("Y-m-d")));
$cd = $c->fetchAllTest("select * from custom_delivery where left(signature,4 = '�PNG' limit 200"));
$c->logMessage(__FUNCTION__,sprintf("returned [%s] records", count($cd)),1);

foreach($cd as $k=>$v) {
	//$c->logMessage(__FUNCTION__,sprintf("cd [%s]", print_r($v,true)), 1);
	if (substr($v["signature"],0,4) == "�PNG") {
		$c->logMessage(__FUNCTION__,sprintf("process it"),1);
		$image = imagecreatefromstring($v["signature"]);
		$bg = imagecreatetruecolor(imagesx($image), imagesy($image));
		imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
		imagealphablending($bg, TRUE);
		imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
		imagedestroy($image);
		$quality = 80;
		imagejpeg($bg, "test.jpg", $quality);
		imagedestroy($bg);
		$img = file_get_contents("test.jpg");
		$c->logMessage(__FUNCTION__,sprintf("png [%s] jpg [%s] id [%d]", strlen($v["signature"]), strlen($img), $v["id"]),1);
		$stmt = $c->prepare(sprintf("update custom_delivery set signature = ? where id = ?"));
		$stmt->bindParams(array("sd", $img, $v["id"]));
		$stmt->execute();
echo sprintf("converted %d<br/>", $v["id"]).PHP_EOL;
	}
}
echo "done".PHP_EOL;
?>
