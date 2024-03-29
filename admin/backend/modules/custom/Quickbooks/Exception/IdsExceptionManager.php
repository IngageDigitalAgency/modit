<?php 

require_once(PATH_SDK_ROOT . 'Exception/IdsException.php');

/**
 * Manages all the exceptions thrown to the user.
 */
class IdsExceptionManager
{
    /**
     * Handles exception thrown to the user.
     * @param string errorMessage Error Message
     * @throws IdsException
     */
	public static function HandleExceptionMessage($errorMessage)
	{
	    throw new IdsException($errorMessage);
	}
	
	/**
	 * Handles Exception thrown to the user.
	 * @param IdsException idsException Ids Exception
	 */
	public static function HandleExceptionObject($idsException)
	{
	    throw $idsException;
	}

    /**
     * Handles Exception thrown to the user.
     * @param null $errorMessage
     * @param null $errorCode
     * @param null $source
     * @param null $innerException
     * @throws IdsException
     */
	public static function HandleException($errorMessage=NULL, $errorCode=NULL, $source=NULL, $innerException=NULL)
	{
		$message = implode(", ", array($errorMessage, $errorCode, $source));
		throw new IdsException($message);
	}    
    
}

?>
