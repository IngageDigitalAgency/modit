<?php

session_start();
ob_start();
$st = explode(' ',microtime());

//using package manger to load third library auomatically
require __DIR__.'/../vendor/autoload.php';
require_once("config.php");
require_once("classes/globals.php");
require_once("classes/PHPMailer/PHPMailerAutoload.php");
require_once("classes/common.php");
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
$be = new Backend(true);
echo $be->show();
$be = null;
$et = explode(' ',microtime());
echo sprintf('<!-- render time is %f seconds -->',$et[1] - $st[1] + $et[0] - $st[0]);
ob_end_flush();

?>
