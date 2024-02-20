<?php
// Copyright 2009, FedEx Corporation. All rights reserved.
// Version 6.0.0

require_once('../../library/fedex-common.php5');

//The WSDL is not included with the sample code.
//Please include and reference in $path_to_wsdl variable.
$path_to_wsdl = "../../wsdl/TrackService_v8.wsdl";

define('SPOD_LABEL', 'spodlabel.pdf');  // PDF label file. 
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
$request['TransactionDetail'] = array(
	'CustomerTransactionId' => '*** SPOD Request v8 using PHP ***',
	'Localization' => array(
		'LanguageCode'=>'EN'
	)
);
$request['Version'] = array(
	'ServiceId' => 'trck', 
	'Major' => '8', 
	'Intermediate' => '0', 
	'Minor' => '0'
);
$request['QualifiedTrackingNumber'] = array (
	'TrackingNumber' => getProperty('trackingnumber'), // Replace 'XXX' with actual tracking number
	'ShipDate' => getProperty('spodshipdate'),
	'AccountNumber' => getProperty('trackaccount'),
	'Carrier' => 'FDXE'
);
//$request['AdditionalInformation'] = 'NONE';
$request['FaxSender'] = array(
	'Contact' => array(
		'PersonName' => 'Ralph Johnson',
		'CompanyName' => 'Farmers Supply',
		'Department' => 'Accounting',
		'PhoneNumber' => '9015550001',
		'FaxNumber' => '9015550001'
	),
	'Address' => array(
		'StreetLines' => array('1950 South Beach'),
		'City' => 'Miami',
		'StateOrProvinceCode' => 'FL',
		'PostalCode' => '34091',
		'CountryCode' => 'US'
	)
);
$request['FaxRecipient'] = array(
	'Contact' => array(
		'PersonName' => 'Ralph Johnson',
		'CompanyName' => 'Farmers Supply',
		'Department' => 'Accounting',
		'PhoneNumber' => '9015550001',
		'FaxNumber' => '9015550001'
	),
	'Address' => array(
		'StreetLines' => array('1950 South Beach'),
		'City' => 'Miami',
		'StateOrProvinceCode' => 'FL',
		'PostalCode' => '34091',
		'CountryCode' => 'US'
	)
);



try {
	if(setEndpoint('changeEndpoint')){
		$newLocation = $client->__setLocation(setEndpoint('endpoint'));
	}
	
	$response = $client ->sendSignatureProofOfDeliveryFax($request);

    if ($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR'){
        if(array_key_exists('FaxConfirmationNumber', $response)){
        	echo 'Fax Confirmation Number is '. $response->FaxConfirmationNumber;
        }
        printSuccess($client, $response);
    }else{
        printError($client, $response);
    } 
    
    writeToLog($client);    // Write to log file   
} catch (SoapFault $exception) {
    printFault($exception, $client);
}
?>