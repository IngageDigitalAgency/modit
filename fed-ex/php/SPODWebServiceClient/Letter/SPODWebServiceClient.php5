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
	'MeterNumber' => getProperty('meter'));
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
	'AccountNumber' => getProperty('trackaccount')
);
$request['AdditionalComments'] = 'NONE';
$request['LetterFormat'] = 'PDF';  
$request['Consignee'] = array(
	'Contact' => array(
		'PersonName' => 'John Smith',
		'CompanyName' => 'Company Name',
		'PhoneNumber' => '4075551212'
	),
	'Address' => array(
		'StreetLines' => array('123 S. Main St'),
		'City' => 'Lake Mary',
		'StateOrProvinceCode' => 'FL',
		'PostalCode' => '32746',
		'CountryCode' => 'US'
	)
);   


                                                                                                        
try {
	if(setEndpoint('changeEndpoint')){
		$newLocation = $client->__setLocation(setEndpoint('endpoint'));
	}
	
	$response = $client ->retrieveSignatureProofOfDeliveryLetter($request);

    if ($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR'){
        $fp = fopen(SPOD_LABEL, 'wb');   
        fwrite($fp, $response->Letter); //Create COD Return PNG or PDF file
        fclose($fp);
        echo '<a href="./'.SPOD_LABEL.'">'.SPOD_LABEL.'</a> was generated.'.Newline;

        printSuccess($client, $response);
    }else{
        printError($client, $response);
    } 
    
    writeToLog($client);    // Write to log file   
} catch (SoapFault $exception) {
    printFault($exception, $client);
}
?>
