<?php

require_once(PATH_SDK_ROOT . 'DataService/IntuitBatchResponse.php');
require_once(PATH_SDK_ROOT . 'DataService/IntuitResponseStatus.php');
require_once(PATH_SDK_ROOT . 'Utility/Serialization/XmlObjectSerializer.php');
require_once(PATH_SDK_ROOT . 'Exception/IdsExceptionManager.php');
require_once(PATH_SDK_ROOT . 'Exception/IdsError.php');
require_once(PATH_SDK_ROOT . 'Exception/ValidationException.php');
require_once(PATH_SDK_ROOT . 'Exception/ServiceException.php');
require_once(PATH_SDK_ROOT . 'Exception/SecurityException.php');

/**
 * Describes operations that can be included in a batch
 */
class OperationEnum {
	
	/**
	 * create Operation
	 * @var string create
	 */
	const create = "create";	
	
	/**
	 * update Operation
	 * @var string update
	 */
	const update = "update";	

	/**
	 * sparse update Operation
	 * @var string sparseupdate
	 */
	const sparseupdate = "sparse update";	

	/**
	 * delete Operation
	 * @var string delete
	 */
	const delete = "delete";	

	/**
	 * void Operation
	 * @var string void
	 */
	const void = "void";	

	/**
	 * query Operation
	 * @var string query
	 */
	const query = "query";	


	/**
	 * report Operation
	 * @var string report
	 */
	const report = "report";	
}

/**
 * This class contains code for Batch Processing.
 */
class Batch {

	/**
	 * batch requests
	 * @var array batchRequests
	 */
	private $batchRequests;

	/**
	 * batch responses
	 * @var array batchResponses
	 */
	private $batchResponses;

	/**
	 * Intuit batch item responses list.
	 * @var array batchResponses
	 */
	public $intuitBatchItemResponses;

	/**
	 * service context object.
	 * @var ServiceContext serviceContext
	 */
	private $serviceContext;

	/**
	 * rest handler object.
	 * @var IRestHandler restHandler
	 */
	private $restHandler;

	/**
	 * serializer to be used.
	 * @var IEntitySerializer responseSerializer
	 */
	private $responseSerializer;

	/**
	 * Initializes a new instance of the Batch class.
	 * @param $serviceContext The service context.
	 * @param $restHandler The rest handler.
	 */
	public function __construct($serviceContext, $restHandler)
	{
		$this->serviceContext = $serviceContext;
		$this->restHandler = $restHandler;
		$this->responseSerializer = CoreHelper::GetSerializer($this->serviceContext, false);
		$this->batchRequests = array();
		$this->batchResponses = array();
		$this->intuitBatchItemResponses = array();
	}

	/**
	 * Gets the count.
	 * @return int count
	 */
	public function Count()
	{
		return count($this->batchRequest);
	}
	
	/**
	 * Gets list of entites in case ResponseType is Report.
	 */
	public function ReadOnlyCollection()
	{
		return $this->intuitBatchItemResponses;
	}
	
	/**
	 * Gets the IntuitBatchResponse with the specified id.
	 * @param string $id unique batchitem id
	 */
	public function IntuitBatchResponse($id)
	{
		foreach($this->batchResponses as $oneBatchResponse)
		{
			if ($oneBatchResponse->bId == $id)
			{
				$result = ProcessBatchItemResponse($oneBatchResponse);
				return $result;
			}
		}
		return NULL;
	}

    /**
     * Adds the specified query.
     * @param string $query IDS query.
     * @param string $id unique batchitem id.
     * @throws IdsException
     */
	public function AddQuery($query, $id)
	{
		if (!$query)
		{
			$exception = new IdsException('StringParameterNullOrEmpty: query');
			IdsExceptionManager::HandleException($exception);
		}	
		
		if (!$id)
		{
			$exception = new IdsException('StringParameterNullOrEmpty: id');
			IdsExceptionManager::HandleException($exception);
		}	
		
		if (count($this->batchRequests)>25)
		{
			$exception = new IdsException('BatchItemsExceededException');
			IdsExceptionManager::HandleException($exception);
		}
		
		$batchItem = new IPPBatchItemRequest();
		$batchItem->Query = $query;
		$batchItem->bId = $id;
		$batchItem->operationSpecified = true;
		//$batchItem->ItemElementName = ItemChoiceType6::Query;
		$this->batchRequests[] = $batchItem;
	}


    /**
     * Adds the specified query.
     * @param $entity
     * @param $id
     * @param $operation
     * @throws IdsException
     */
	 public function AddEntity($entity, $id, $operation)
	 {
		if (!$entity)
		{
			$exception = new IdsException('StringParameterNullOrEmpty: entity');
			IdsExceptionManager::HandleException($exception);
		}	
		
		if (!$id)
		{
			$exception = new IdsException('StringParameterNullOrEmpty: id');
			IdsExceptionManager::HandleException($exception);
		}	
		
		if (!$operation)
		{
			$exception = new IdsException('StringParameterNullOrEmpty: operation');
			IdsExceptionManager::HandleException($exception);
		}	
		
		foreach($this->batchRequests as $oneBatchRequest)
		{
			if ($oneBatchRequest->bId == $id)
			{
				$exception = new IdsException('BatchIdAlreadyUsed');
				IdsExceptionManager::HandleException($exception);
			}
		}

		$batchItem = new IPPBatchItemRequest();
		$batchItem->IntuitObject = $entity;
		$batchItem->bId = $id;
		$batchItem->operation = $operation;
		$batchItem->operationSpecified = true;

		$this->batchRequests[] = $batchItem;		
	 }


    /**
     * Removes batchitem with the specified batchitem id.
     * @param string id unique batchitem id
     * @throws IdsException
     */
	public function Remove($id)
	{
		if (!$id)
		{
			$exception = new IdsException('BatchItemIdNotFound: id');
			IdsExceptionManager::HandleException($exception);
		}	
		
		$revisedBatchRequests = array();
		foreach($this->batchRequests as $oneBatchRequest)
		{
			if ($oneBatchRequest->bId == $id)
			{
				// Exclude
			}
			else
			{
				$revisedBatchRequests[] = $oneBatchRequest;
			}
		}
	 	$this->batchRequests = $revisedBatchRequests;
	}
	 
	/**
	 * Remove all the batchitem requests.
	 */	
	public function RemoveAll()
	{
		$this->batchRequests = array();
	}
	
	
	/**
	 * This method executes the batch request.
	 */
	public function Execute()
	{
        $this->serviceContext->IppConfiguration->Logger->CustomLogger->Log(TraceLevel::Info, "Started Executing Method Execute for Batch");

		// Create Intuit Batch Request
		$intuitBatchRequest = new IPPIntuitBatchRequest();
		$intuitBatchRequest->BatchItemRequest = $this->batchRequests;
		
		$uri = "company/{1}/batch?requestid=" . random_int(0,99) . random_int(0,999);
		$uri = str_replace('{1}', $this->serviceContext->realmId, $uri);

        // Creates request parameters
        $requestParameters = NULL;
		if (0) // ($this->serviceContext->IppConfiguration->Message->Request->SerializationFormat == SerializationFormat::Json)
		{
	        // No JSON support here yet
			//$requestParameters = new RequestParameters($uri, 'POST', CoreConstants::CONTENTTYPE_APPLICATIONJSON, NULL);
		}
		else
		{
			$requestParameters = new RequestParameters($uri, 'POST', CoreConstants::CONTENTTYPE_APPLICATIONXML, NULL);
		}

                $restRequestHandler = $this->getRestHandler();
		try
		{
			// Get literal XML representation of IntuitBatchRequest into a DOMDocument
			$httpsPostBodyPreProcessed = XmlObjectSerializer::getPostXmlFromArbitraryEntity($intuitBatchRequest, $urlResource);
			$doc = new DOMDocument();
			$domObj = $doc->loadXML($httpsPostBodyPreProcessed);
			$xpath = new DOMXpath($doc);

			// Replace generically-named IntuitObject nodes with tags that describe contained objects
			$objectIndex = 0;
			while (1)
			{
				$matchingElementArray = $xpath->query("//IntuitObject");
				if (is_null($matchingElementArray))
					break;
					
				if ($objectIndex>=count($intuitBatchRequest->BatchItemRequest))
					break;
			
				foreach ($matchingElementArray as $oneNode) {
				
					// Found a DOMNode currently named "IntuitObject".  Need to rename to
					// entity that describes it's contents, like "ns0:Customer" (determine correct
					// name by inspecting IntuitObject's class).
					if ($intuitBatchRequest->BatchItemRequest[$objectIndex]->IntuitObject)
					{
						// Determine entity name to use					
						$entityClassName = get_class($intuitBatchRequest->BatchItemRequest[$objectIndex]->IntuitObject);
						$entityTransferName = XmlObjectSerializer::cleanPhpClassNameToIntuitEntityName($entityClassName);
						$entityTransferName = 'ns0:'.$entityTransferName;
						
						// Replace old-named DOMNode with new-named DOMNode
						$newNode = $oneNode->ownerDocument->createElement($entityTransferName);
						if ($oneNode->attributes->length) {
							foreach ($oneNode->attributes as $attribute) {
								$newNode->setAttribute($attribute->nodeName, $attribute->nodeValue);
							}
						}
						while ($oneNode->firstChild)
							$newNode->appendChild($oneNode->firstChild);
						$oneNode->parentNode->replaceChild($newNode, $oneNode);
					}
					break;
				}
				$objectIndex++;
			}
			$httpsPostBody = $doc->saveXML();

			list($responseCode,$responseBody) = $restRequestHandler->GetResponse($requestParameters, $httpsPostBody, NULL);
		}
		catch (Exception $e)
		{
            IdsExceptionManager::HandleException($e);
		}		
		
        CoreHelper::CheckNullResponseAndThrowException($responseBody);

		try {

			$this->batchResponses = array();
			$this->intuitBatchItemResponses = array();
			
	        // No JSON support here yet
			// de serialize object
			$responseXmlObj = simplexml_load_string($responseBody);
			foreach($responseXmlObj as $oneXmlObj)
			{
				// process batch item
				$intuitBatchResponse = $this->ProcessBatchItemResponse($oneXmlObj);
				$this->intuitBatchItemResponses[] = $intuitBatchResponse;
				
				if ($intuitBatchResponse && $intuitBatchResponse->entities && count($intuitBatchResponse->entities))
					$this->batchResponses[] = $intuitBatchResponse->entities;
			}
		}
		catch (Exception $e) {
                    var_dump($e->getMessage(), $e->getLine());
			return NULL;
		}		

        $this->serviceContext->IppConfiguration->Logger->CustomLogger->Log(TraceLevel::Info, "Finished Execute method for batch.");
	}
        
        
        /**
         * Returns handler to communicate with service
         * @return \SyncRestHandler
         */
        protected function getRestHandler() 
        {
           return new SyncRestHandler($this->serviceContext);
        }

    /**
     * @param $fault
     * @return bool|null
     */
    private function verifyFault($fault)
        {
            if ( $fault == NULL )       { return NULL; } 
            if ( empty($fault)  )       { return NULL; }
            if ( !$fault instanceof SimpleXMLElement)               { return NULL; }
            if ( !$fault->attributes() instanceof SimpleXMLElement) { return NULL; }
            if ( !isset($fault->attributes()->type))                { return NULL; }
            
            return true;
        }

    /**
     * @param $fault
     * @return array
     */
    private function collectErrors($fault)
        {
            $errors = array();
            if(isset($fault->Error) 
                && ($fault->Error instanceof SimpleXMLElement)
                && $fault->Error->count()) {
                foreach ($fault->Error as $item) {
                    if(!isset($item->Message)) { continue; }
                    if(!$item->Message instanceof SimpleXMLElement) { continue; }
                    $error = new stdClass();
                    $error->message = (string)$item->Message;
                    $error->code = null;
                    if ($item->attributes() instanceof SimpleXMLElement
                            && isset($item->attributes()->code)) {
                        $error->code = (string)$item->attributes()->code;
                    }
                    $errors[] =$error;
                }
            }
            return $errors;
        }

    /**
     * @param array $array
     * @return array|null[]
     */
    private function arrayToMessageAndCode(array $array)
        {
            if(empty($array)) {
                return array(null,null);
            }
            if(1 == count($array)) {
                $item = array_pop($array);
                return array($item->message,$item->code);
            } 
            
            $message = "";
            $code = "";
            foreach ($array as $item) {
                $message .= "Exception: ".$item->message . "\n";
                if(empty($code) && !empty($item->code)) {
                    $code = $item->code;
                }
            }
            return array($message,$code);
            
        }
	
	/**
	 * Prepare IdsException out of Fault object.
	 * @param Fault fault Fault object.
	 * @return IdsException IdsException object.
	 */
	 public function IterateFaultAndPrepareException($fault) {
            if(!$this->verifyFault($fault)) { return NULL; }    
            // Collect information from XML entity
            $type = (string)$fault->attributes()->type;
            list($message,$code) = $this->arrayToMessageAndCode($this->collectErrors($fault));
            if(is_null($message)) {
                return new IdsException("Fault Exception of type: " . $type . " has been generated.");
            }
            $idsException = null;


            // Fault types can be of Validation, Service, Authentication and Authorization. Run them through the switch case.
            switch ($type) {
                // If Validation errors iterate the Errors and add them to the list of exceptions.
                case "Validation":
                case "ValidationFault":
                        // Throw specific exception like ValidationException.
                        $idsException = new ValidationException($message,$code);
                    break;
                // If Validation errors iterate the Errors and add them to the list of exceptions.
                case "Service":
                case "ServiceFault":
                        // Throw specific exception like ServiceException.
                        $idsException = new ServiceException($message,$code);
                    break;
                // If Validation errors iterate the Errors and add them to the list of exceptions.
                case "Authentication":
                case "AuthenticationFault":
                case "Authorization":
                case "AuthorizationFault":
                        $idsException = new SecurityException($message,$code);
                    break;
                // Use this as default if there was some other type of Fault
                default:
                        $idsException = new IdsException($message,$code);
                    
            }


        // Return idsException which will be of type Validation, Service or Security.
        return $idsException;
    }

    /**
     * process batch item response
     * @param BatchItemResponse oneXmlObj The batchitem response.
     * @return IntuitBatchResponse IntuitBatchResponse object.
     * @throws IdsException
     * @throws ReflectionException
     */
    private function ProcessBatchItemResponse($oneXmlObj)
    {
        $result = new IntuitBatchResponse();
        if (NULL==$oneXmlObj)
        	return $result;

		$firstChild = NULL;
		foreach($oneXmlObj->children() as $oneChild)
		{
			$firstChild = $oneChild;
			break;
		}
		if (!$firstChild)
			return NULL;
		
		$firstChildName = (string)$firstChild->getName();
                //add batch id
                $this->applyAttributes($result, $oneXmlObj);

		if(0 !== strcmp("Fault",$firstChildName))
		{
			if ('QueryResponse'==$firstChildName)
			{
				foreach($oneXmlObj->QueryResponse->children() as $oneResponse)
				{
				    $result->responseType = ResponseType::Query;
					$oneEntity = $this->responseSerializer->Deserialize('<RestResponse>'.$oneResponse->asXML().'</RestResponse>');
					$result->AddEntities($oneEntity);
				}
			}
			else
			{
				$oneEntityArray = $this->responseSerializer->Deserialize('<RestResponse>'.$firstChild->asXML().'</RestResponse>');
				$oneEntity = $oneEntityArray[0];
				$result->entity = $oneEntityArray[0];
				$result->responseType = ResponseType::Entity;
			}
		}
		else
		{
                    
			$result->responseType = ResponseType::Exception;
			$idsException = $this->IterateFaultAndPrepareException($firstChild);
			$result->exception = $idsException;
		}
		
        return $result;
    }
    
    /**
     * Maps some values from the response xml to the result instance object
     * @param IntuitBatchResponse $batchResponse
     * @param SimpleXMLElement $simpleXML
     */
    private function applyAttributes($batchResponse, $simpleXML)
    {
        // To support PHP 5.3
         $attributes = $simpleXML->attributes();
         if(!empty($attributes) && isset($attributes["bId"])) {
             $batchResponse->batchItemId = (string)$attributes["bId"];
         }     
    }
}
