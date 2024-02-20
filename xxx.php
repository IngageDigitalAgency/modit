<?php

if (file_exists("./maintenance.html")) {
	$html = file_get_contents("./maintenance.html");
	echo $html;
	exit;
}

session_start();
ob_start();
$st = microtime();
require_once("config.php");
require_once(ADMIN."config.php");
require_once(ADMIN."classes/globals.php");
require_once(ADMIN."classes/PHPMailer/PHPMailerAutoload.php");
require_once(ADMIN."classes/common.php");
require_once(ADMIN."classes/Forms.php");
require_once(ADMIN."classes/HtmlElement.php");
require_once(ADMIN."classes/mptt.php");
require_once(ADMIN."classes/Snoopy.php");
//require_once(ADMIN."classes/tcpdf/tcpdf.php"); //updating to latest version using php packagemanger ,so it will load automatically
require_once(ADMIN."classes/KJV.php");
require_once(ADMIN."frontend/Frontend.php");

date_default_timezone_set(TZ);
setlocale(LC_MONETARY,CURRENCY);
$fe = new Frontend(true);
$fe->logMessage(__FUNCTION__,sprintf("live test 2"),1,true,true);
ob_end_flush();

?>
