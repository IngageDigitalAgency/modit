<?php

//using package manger to load library auomatically
//include "./admin/classes/quickbooks/autoload.php";

require 'vendor/autoload.php';

require_once("config.php");
require_once(ADMIN."config.php");
require_once(ADMIN."classes/globals.php");
require_once(ADMIN."classes/mailer.php");
require_once(ADMIN."classes/common.php");
require_once(ADMIN."classes/smtp.php");
require_once(ADMIN."classes/Forms.php");
require_once(ADMIN."classes/HtmlElement.php");
require_once(ADMIN."classes/Snoopy.php");

use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;

$c = new Common(true);
session_start();

echo "<html><head></head><body>";
//error_reporting(E_ALL);
echo "test 1<br/>";
if (count($_REQUEST) > 0 && array_key_exists("state",$_REQUEST) && array_key_exists("code",$_REQUEST)) {
echo "test 2<br/>";
$c->logMessage(__FUNCTION__,sprintf("request [%s]\nsession [%s]", print_r($_REQUEST,true), print_r($_SESSION,true)),1);	
if ($_REQUEST["state"] == $_SESSION["administrator"]["qb"]["state"]) {
echo "test 3<br/>";
		$dataService = DataService::Configure(array(
			'auth_mode' => 'oauth2',
			'ClientID' => $GLOBALS["quickbooks"]["client_id"],
			'ClientSecret' => $GLOBALS["quickbooks"]["client_secret"],
			'RedirectURI' => $GLOBALS["quickbooks"]["oauth_redirect"],
			'scope' => $GLOBALS["quickbooks"]["oauth_scope"],
			'baseUrl' => $GLOBALS["quickbooks"]["baseUrl"]
		));
	
		$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
	
		$url = $OAuth2LoginHelper->getAuthorizationCodeURL();
		$c->logMessage(__FUNCTION__,sprintf("data service [%s] login Helper [%s]", print_r($dataService,true), print_r($OAuth2LoginHelper,true)),4); 
		$accessToken = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($_REQUEST["code"], $_REQUEST["realmId"]);
		$c->logMessage(__FUNCTION__,sprintf("token 1 [%s]", print_r($accessToken,true)), 4);
		$dataService->updateOAuth2Token($accessToken);
		$c->logMessage(__FUNCTION__,sprintf("token 2 [%s] data service [%s]", print_r($accessToken,true), print_r($dataService,true)), 3);
		$dataService->throwExceptionOnError(true);
		$CompanyInfo = $dataService->getCompanyInfo();
		$nameOfCompany = $CompanyInfo->CompanyName;
		$_SESSION["administrator"]["qb"]["accessToken"] = $accessToken->getAccessToken();
		$_SESSION["administrator"]["qb"]["refreshToken"] = $accessToken->getRefreshToken();
		$_SESSION["administrator"]["qb"]["realmId"] = $accessToken->getRealmId();
		$_SESSION["administrator"]["qb"]["expiry"] = date(DATE_ATOM,strtotime($accessToken->getAccessTokenExpiresAt()));
		$c->logMessage(__FUNCTION__,sprintf("accesstoken [%s] session is [%s]", print_r($accessToken,true), print_r($_SESSION["administrator"],true)), 1);
		echo sprintf("<script type='text/javascript'>document.location = '//%s/modit/credit/qb'</script>",HOSTNAME).PHP_EOL;
	}
}
echo "</body></html>";
?>
