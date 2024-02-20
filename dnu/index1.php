<?php

session_start();
ob_start();
$st = microtime();
require_once("config.php");
require_once(ADMIN."config.php");
require_once(ADMIN."classes/globals.php");
require_once(ADMIN."classes/PHPMailer/PHPMailerAutoload.php");
require_once(ADMIN."classes/common.php");
/*require_once(ADMIN."classes/smtp.php");*/
require_once(ADMIN."classes/Forms.php");
require_once(ADMIN."classes/HtmlElement.php");
require_once(ADMIN."classes/mptt.php");
require_once(ADMIN."classes/Snoopy.php");
require_once(ADMIN."classes/tcpdf/tcpdf.php");
require_once(ADMIN."frontend/Frontend.php");

date_default_timezone_set(TZ);
setlocale(LC_MONETARY,CURRENCY);
$fe = new Frontend(true);
echo $fe->show();
$fe = null;
$et = microtime();
//echo sprintf('<!-- render time is %f seconds -->',$et - $st);
ob_end_flush();

?>
