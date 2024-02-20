<?php


session_start();
ob_start();
$st = explode(' ',microtime());
set_time_limit(60*10);
require_once("config.php");
require_once(ADMIN."config.php");
error_reporting(E_ALL);
require_once(ADMIN."classes/globals.php");
require_once(ADMIN."classes/mailer/PHPMailer.php");
require_once(ADMIN."classes/mailer/SMTP.php");
require_once(ADMIN."classes/mailer/Exception.php");
require_once(ADMIN."classes/mailer.php");
require_once(ADMIN."classes/smtp.php");
require_once(ADMIN."classes/common.php");
require_once(ADMIN."classes/Forms.php");
require_once(ADMIN."classes/HtmlElement.php");
require_once(ADMIN."frontend/Frontend.php");
require_once(ADMIN."classes/tcpdf/tcpdf.php");
require_once(ADMIN."frontend/modules/custom.php");


class tempMailer extends PHPMailer\PHPMailer\PHPMailer {
	function __construct() {
		parent::__construct();
		if (array_key_exists('mail',$GLOBALS)) {
			if ($GLOBALS['mail']['type'] == 'smtp') {
				$this->isSMTP();
				if (array_key_exists('user',$GLOBALS['mail'])) {
					$this->Username = $GLOBALS['mail']['user'];
					$this->Host = $GLOBALS['mail']['host'];
					$this->Port = $GLOBALS['mail']['port'];
					$this->Password= $GLOBALS['mail']['password'];
					$this->SMTPAuth = true;
				}
			}
			else {
				$this->isMail();
			}
		}
		else {
			if (MAILTYPE == 'smtp') {
				$this->isSMTP();
				if (DEFINED('MAILUSER')) $this->Username = MAILUSER;
				if (DEFINED('MAILHOST')) $this->Host = MAILHOST;
				if (DEFINED('MAILPORT')) $this->Port = MAILPORT;
				if (DEFINED('MAILPASSWORD')) {
					$this->Password = MAILPASSWORD;
					$this->SMTPAuth = true;
				}
			}
			if (MAILTYPE == 'mail') $this->isMail();
		}
	}
}

date_default_timezone_set(TZ);
setlocale(LC_MONETARY,CURRENCY);

$c = new Common(true,false,sprintf("INV-%s",date("Y-m-d")));
$c->setDebug(2);
error_reporting(E_ALL & ~ E_STRICT);
$recs = $c->fetchAll(sprintf("select *,invoice_amount+tax_amount as invoice_total from qb_export where invoice_date='2021-09-04'"));
foreach($recs as $k=>$v) {
	$member = $c->fetchSingle(sprintf("select * from members where id = %d", $v["member_id"]));
	$c->m_module = array();
	$c->config = new Custom(0);
	$c->logMeIn($member["email"],$member["password"]);
	$sql = 'select p.*, t.module_function, m.classname, m.id as module_id from modules_by_page p left join fetemplates t on t.id = p.fetemplate_id left join modules m on m.id = t.module_id where p.page_type = "P" and p.page_id = 163 and p.module_name = "main1"';
	$module = $c->fetchSingle($sql);
	$class = new Custom($module['id'],$module);
	$class->config = new Custom(0);
	$_REQUEST["i_id"] = $v["id"];
	$pdf = $class->{$module['module_function']}(true, sprintf("files/invoice-%d.pdf",$v["qb_invoice_id"]));
if (DEV==0)
	$mailer = new MyMailer();
else
	$mailer = new tempMailer();
	$mailer->Subject = sprintf("Order Processing - %s", SITENAME);
	$body = new Forms();
	$flds = array("invoice_date"=>array("type"=>"datestamp"),"invoice_total"=>array("type"=>"currency"));
	$flds = $body->buildForm($flds);
	$sql = sprintf('select * from htmlForms where class = %d and type = "invoiceEmail"',$c->getClassId('custom'));
	$html = $c->fetchSingle($sql);
	$body->setHTML($html['html']);
	$v["user"] = $_SESSION["user"]["info"];
	$v["address"] = Address::formatData($c->fetchSingle(sprintf("select * from addresses where ownertype='member' and ownerid = %d", $v["member_id"])));
	$body->addData($v);
	$body->setOption('formDelimiter','{{|}}');
	$mailer->Body = $body->show();
if (DEV==0) {
	$mailer->From = "noreply@kjvcourier.com";
	$mailer->FromName = "KJV Courier Services";
}
else {
	$mailer->FromName = "ian@kjvcourier.com";
	$mailer->From = "noreply@".HOSTNAME;
}
	$mailer->IsHTML(true);
	if (DEV==0) {
		$mailer->addAddress("ian@kjvcourier.com","Ian MacArthur");
		$mailer->addAddress("vpileggi@kjvcourier.com","Victor Pileggi");
		$mailer->addAddress("lpileggi@kjvcourier.com","Lisa Pileggi");
	}
	else {
		$mailer->addAddress("ian@kjvcourier.com","Ian MacArthur");
		//$mailer->addAddress("vpileggi@kjvcourier.com","Victor Pileggi");
	}
	$mailer->AddAttachment(sprintf("files/invoice-%d.pdf",$v["qb_invoice_id"]),sprintf("invoice-%d.pdf",$v["qb_invoice_id"]));
	$mailer->Subject = sprintf("KJV Courier Invoice #%d - %s", $v["qb_invoice_id"], $v["user"]["company"]);
	if (!$mailer->Send())
		$c->logMessage(__FUNCTION__,sprintf("mailer [%s] [%s]", $mailer->ErrorInfo, print_r($mailer,true)),1);
	unlink(sprintf("files/invoice-%d.pdf",$v["qb_invoice_id"]));
}
?>
