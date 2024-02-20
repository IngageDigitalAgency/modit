<?php

session_start();
ob_start();
$st = microtime();

require_once("config.php");
require_once("classes/globals.php");
require_once("classes/mailer.php");
require_once("classes/common.php");
require_once("classes/smtp.php");
require_once("classes/Forms.php");
require_once("classes/HtmlElement.php");
require_once("classes/mptt.php");
require_once("classes/Snoopy.php");
#require_once("classes/tcpdf/tcpdf.php");
require_once("classes/KJV.php");
require_once("frontend/Frontend.php");

date_default_timezone_set(TZ);
setlocale(LC_MONETARY,CURRENCY);
$fe = new Frontend(true);
echo $fe->render(true);
$fe = null;
$et = microtime();
//echo sprintf('<!-- render time is %f seconds -->',$et - $st);
ob_end_flush();

?>
