<?php
// Copyright 2009, FedEx Corporation. All rights reserved.
// Version 12.0.0

define('TZ','America/New_York');
date_default_timezone_set(TZ);
require_once('../library/fedex-common.php5');

//The WSDL is not included with the sample code.
//Please include and reference in $path_to_wsdl variable.
$path_to_wsdl = "../wsdl/RateService_v14.wsdl";

ini_set("soap.wsdl_cache_enabled", "0");
 
$client = new SoapClient($path_to_wsdl, array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information

$request['WebAuthenticationDetail'] = array(
	'UserCredential' => array(
		'Key' => getProperty('key'), 
		'Password' => getProperty('password')
	)
); 
$request['ClientDetail'] = array(
	'AccountNumber' => getProperty('shipaccount'), 
	'MeterNumber' => getProperty('meter')
);
$request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Rate Available Services Request v14 using PHP ***');
$request['Version'] = array(
	'ServiceId' => 'crs', 
	'Major' => '14', 
	'Intermediate' => '0', 
	'Minor' => '0'
);
$request['RequestedShipment']['PreferredCurrency'] = 'CAD';
$request['ReturnTransitAndCommit'] = true;
$request['RequestedShipment']['DropoffType'] = 'REGULAR_PICKUP'; // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
$request['RequestedShipment']['ShipTimestamp'] = date('c');
// Service Type and Packaging Type are not passed in the request
$tmp = getProperty('shipper');
$request['RequestedShipment']['Shipper'] = array(
	'Address'=>$tmp['Address']
);
$tmp = getProperty('recipient');
$request['RequestedShipment']['Recipient'] = array(
	'Address'=>$tmp['Address']
);
$request['RequestedShipment']['ShippingChargesPayment'] = array(
	'PaymentType' => 'SENDER',
   	'Payor' => array(
		'ResponsibleParty' => array(
			'AccountNumber' => getProperty('billaccount'),
			'Currency'=>'CAD',
			'Contact' => null,
			'Address' => array(
				'CountryCode' => 'CA'
			)
		)
	)
);																
$request['RequestedShipment']['RateRequestTypes'] = 'ACCOUNT'; 
$request['RequestedShipment']['RateRequestTypes'] = 'LIST'; 
$request['RequestedShipment']['PackageCount'] = '2';
$request['RequestedShipment']['RequestedPackageLineItems'] = array(
	'0' => array(
		'SequenceNumber' => 1,
		'GroupPackageCount' => 1,
		'Weight' => array(
			'Value' => 2.0,
	    	'Units' => 'LB'
	    ),
	    'Dimensions' => array(
	    	'Length' => 10,
	       	'Width' => 10,
	       	'Height' => 3,
	       	'Units' => 'IN'
	  	)
	),
	'1' => array(
		'SequenceNumber' => 2,
		'GroupPackageCount' => 1,
	    'Weight' => array(
	    	'Value' => 5.0,
	        'Units' => 'LB'
	   	),
	 	'Dimensions' => array(
	 		'Length' => 20,
	        'Width' => 20,
	        'Height' => 10,
	     	'Units' => 'IN'
	     )
	)
);
echo print_r($request,true).PHP_EOL;


try {
	if(setEndpoint('changeEndpoint')){
		$newLocation = $client->__setLocation(setEndpoint('endpoint'));
	}
	
	$response = $client ->getRates($request);
        
    if ($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR'){
        echo 'Rates for following service type(s) were returned.'. Newline. Newline;
        echo '<table border="1">';
        echo '<tr><td>Service Type</td><td>Amount</td><td>Delivery Date</td>';
		if(is_array($response -> RateReplyDetails)){
			foreach ($response -> RateReplyDetails as $rateReply){
				printRateReplyDetails($rateReply);
			}
		}else{
			printRateReplyDetails($response -> RateReplyDetails);          
		}
        echo '</table>'. Newline;
    	printSuccess($client, $response);
    }else{
        printError($client, $response); 
    } 
    
    writeToLog($client);    // Write to log file   
} catch (SoapFault $exception) {
   printFault($exception, $client);        
}



function printRateReplyDetails($rateReply){
	echo '<tr>';
	$serviceType = '<td>'.$rateReply -> ServiceType . '</td>';
	$amount = '<td>$' . number_format($rateReply->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount,2,".",",") . '</td>';
	if(array_key_exists('DeliveryTimestamp',$rateReply)){
		$deliveryDate= '<td>' . $rateReply->DeliveryTimestamp . '</td>';
	}else{
		$deliveryDate= '<td>' . $rateReply->TransitTime . '</td>';
	}
	echo $serviceType . $amount. $deliveryDate;
	echo '</tr>';
}
?>
