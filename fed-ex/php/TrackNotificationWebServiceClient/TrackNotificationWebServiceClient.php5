<?php
// Copyright 2009, FedEx Corporation. All rights reserved.
// Version 6.0.0

require_once('../library/fedex-common.php5');

//The WSDL is not included with the sample code.
//Please include and reference in $path_to_wsdl variable.
$path_to_wsdl = "../wsdl/TrackService_v8.wsdl";

ini_set("soap.wsdl_cache_enabled", "0");

$client = new SoapClient($path_to_wsdl, array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information

$request['WebAuthenticationDetail'] = array(
	'UserCredential' =>array(
		'Key' => getProperty('key'), 
		'Password' => getProperty('password')
	)
);
$request['ClientDetail'] = array(
	'AccountNumber' => getProperty('shipaccount'), 
	'MeterNumber' => getProperty('meter')
);
$request['TransactionDetail'] = array('CustomerTransactionId' => '*** Notification Request v8 using PHP ***');
$request['Version'] = array(
	'ServiceId' => 'trck', 
	'Major' => '8', 
	'Intermediate' => '0', 
	'Minor' => '0'
);
$request['TrackingNumber'] = getProperty('trackingnumber'); // Replace with your tracking number
$request['ShipDateRangeBegin'] = getProperty('begindate'); // Replace with ship date begin
$request['ShipDateRangeEnd'] = getProperty('enddate'); // Replace with ship date end
$request['SenderEMailAddress'] = 'name@company.com';
$request['SenderContactName'] = 'Sender Contact Name';
$request['NotificationDetail'] = array(
	'PersonalMessage' => 'Personal Message',
	'Recipients' => array(
		'0' => array(
			'EMailNotificationRecipientType' => 'RECIPIENT',
			'EMailAddress' => 'recipient@acme.com',
			'NotificationEventsRequested' => array('ON_DELIVERY', 'ON_EXCEPTION', 'ON_SHIPMENT'),
			'Format' => 'HTML'
		)
	)
);



try {
	if(setEndpoint('changeEndpoint')){
		$newLocation = $client->__setLocation(setEndpoint('endpoint'));
	}
	
	$response = $client ->sendNotifications($request);

    if ($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR'){
        printSuccess($client, $response);
    }else{
        printError($client, $response);
    } 
    
    writeToLog($client);    // Write to log file   
} catch (SoapFault $exception) {
    printFault($exception, $client);
}
?>