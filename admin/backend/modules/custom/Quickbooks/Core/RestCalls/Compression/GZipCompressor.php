<?php

require_once(PATH_SDK_ROOT . 'Core/Interface/ICompressor.php');
require_once(PATH_SDK_ROOT . 'Core/RestCalls/Compression/DataCompressionFormat.php');

/**
 * GZip compressor.
 */
class GZipCompressor extends ICompressor {
 
 	const DataCompressionFormat = DataCompressionFormat::GZip;
 	
	/**
	 * Compresses the input byte array into stream.
	 * @param curl_headers Curl headers, pre-compression
	 * @param requestBody POST body, pre-compression
	 */
 	public function Compress(&$curl_headers, &$requestBody)
 	{
		$requestBody = gzencode($requestBody,9);
		$curl_headers['content-encoding']='gzip';			                     
		$curl_headers['content-length']=strlen($requestBody);			                     
 	}
 	
	/**
	 * Prepares a request header that requests compressed output
	 * @param curl_headers Curl headers, pre-compression
	 */
	public function PrepareDecompress(&$curl_headers)
	{
		$curl_headers['accept-encoding'] = 'gzip';
	}


	/**
	 * Decompresses the output response stream.
	 * @param $responseBody
	 * @param $response_headers
	 * @return Decompressed|false|string
	 */
 	public function Decompress($responseBody, $response_headers)
 	{	
		$responseBody = gzinflate(substr($responseBody,10,-8));
 		return $responseBody;
 	}
}
?>
