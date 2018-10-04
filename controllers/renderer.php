<?php
namespace controllers;

class Renderer {

	const STATUS_CODES_TO_MESSAGES = array(

		// [Informational 1xx]

		100=>'100 Continue',

		101=>'101 Switching Protocols',

		// [Successful 2xx]

		200=>'200 OK',

		201=>'201 Created',

		202=>'202 Accepted',

		203=>'203 Non-Authoritative Information',

		204=>'204 No Content',

		205=>'205 Reset Content',

		206=>'206 Partial Content',

		// [Redirection 3xx]

		300=>'300 Multiple Choices',

		301=>'301 Moved Permanently',

		302=>'302 Found',

		303=>'303 See Other',

		304=>'304 Not Modified',

		305=>'305 Use Proxy',

		306=>'306 (Unused)',

		307=>'307 Temporary Redirect',

		// [Client Error 4xx]

		400=>'400 Bad Request',

		401=>'401 Unauthorized',

		402=>'402 Payment Required',

		403=>'403 Forbidden',

		404=>'404 Not Found',

		405=>'405 Method Not Allowed',

		406=>'406 Not Acceptable',

		407=>'407 Proxy Authentication Required',

		408=>'408 Request Timeout',

		409=>'409 Conflict',

		410=>'410 Gone',

		411=>'411 Length Required',

		412=>'412 Precondition Failed',

		413=>'413 Request Entity Too Large',

		414=>'414 Request-URI Too Long',

		415=>'415 Unsupported Media Type',

		416=>'416 Requested Range Not Satisfiable',

		417=>'417 Expectation Failed',

		// [Server Error 5xx]

		500=>'500 Internal Server Error',

		501=>'501 Not Implemented',

		502=>'502 Bad Gateway',

		503=>'503 Service Unavailable',

		504=>'504 Gateway Timeout',

		505=>'505 HTTP Version Not Supported'

	);

	/** We are not allowed to cache responses with statuses other than listed. */
	const STATUS_CODES_ALLOWED_TO_BE_CACHED = array(

		200

		);

	/** Where we will look for a template file. */
	private $_viewDir = null;
	/** Name of a template file. */
	private $_view = null;
	/** Response received from a resource(will be decorated by a view file). */
	private $_response = null;
	/** HTTP status code that will be sent to the client. */
	private $_statusCode = null;
	/** Message of the status code. */
	private $_statusCodeMessage = null;
	/** Time of response(from a resource) last modification. */
	private $_responseLastModified = null;
	/** Time of view file last modification. */
	private $_viewLastModified = null;
	/** If we want to use Last-Modified mechanism(Etag only is used by default). 
		TODO: Last-modified is not working now. Do not enable it now.
	*/
	private $_lastModifiedCachingEnabled = false;


	/**
	  * Init Renderer object with:
	  * -statusCode(200, 404, etc);
	  * -response body itself(users collection, exception message, etc);
	  * -directory where view file will be searched for;
	  * -view file name;
	  *
	  * @param integer $statusCode Contains status code of the response that will be sent 		to client. But sometimes this status code will be changed by renderer(in case 		of errors).
	  * @param string $response Contains response from the requested resource or 				 an exception message for example.
	  * @param string $viewDir Contains name of view directory where we will search
	  		   for the view specified for current rendering process.
	  * @param string $view Contains name of the view specified for the current rendering 			process.
	  * @return void
	  */
	public function __construct($statusCode, $response, $viewDir = 'views/', $view='simple_table')
	{

		$this->_statusCode = intval($statusCode);
		$this->_statusCodeMessage = self::getStatusMessageByCode($this->_statusCode);
		$this->_response = $response;
		$this->_viewDir = $viewDir;
		$this->_view = $view;
		$this->_viewLastModified = filemtime($this->_viewDir . $this->_view . '.php');

	}

	public function setResponseLastModified($lastModified)
	{

		$this->_responseLastModified = $lastModified;
	
	}

	/**
	  * Sends customized response to the client. Also checks if cache can be used.
	  *
	  * @return void
	  */
	public function render()
	{

		$response = $this->_getDecoratedResponse();
		$requestHeaders = getallheaders();

		$cachingAllowed = in_array($this->_statusCode, self::STATUS_CODES_ALLOWED_TO_BE_CACHED);

		if ($cachingAllowed){

			$etagReceived = isset($requestHeaders["If-None-Match"]) ? $requestHeaders["If-None-Match"] : false;
			$etagResponse = md5($response);
			header("Etag: " . $etagResponse);
		
		}

		if ($cachingAllowed && $this->_lastModifiedCachingEnabled){

			$ifModifiedSince = isset($requestHeaders["If-Modified-Since"]) ? $requestHeaders["If-Modified-Since"] : false;

			$contentLastModified = $this->_getContentLastModified();
			
			header( "Last-Modified: ".gmdate( "D, d M Y H:i:s", $contentLastModified )." GMT" );

			if (!is_null($this->_responseLastModified) 
				&& ($etagResponse === $etagReceived) &&
				(intval(strtotime($ifModifiedSince)) === intval($contentLastModified))){

					header("HTTP/1.1 304 Not Modified");
					exit;	
			
			}
		
		} elseif ($cachingAllowed && ($etagResponse === $etagReceived)){
			
			header("HTTP/1.1 304 Not Modified");
			exit;	
		
		}
		
		header("HTTP/1.1 " . $this->_statusCode . " " . self::getStatusMessageByCode($this->_statusCode));
		echo $response;
		exit;
	
	}

	private function _getContentLastModified()
	{

		if(!is_null($this->_responseLastModified)){
			
			if ($this->_viewLastModified > $this->_responseLastModified){
				
				$contentLastModified = $this->_viewLastModified;
			
			} else {
				
				$contentLastModified = $this->_responseLastModified;
			
			}
		
		} else {

			$contentLastModified = $this->_viewLastModified;
		
		}

		return $contentLastModified;
	
	}

	private function _getDecoratedResponse()
	{

		ob_start();
		require $this->_viewDir . $this->_view . '.php';
		$response = ob_get_contents();
		ob_end_clean();
		return $response;
	
	}

	public static function getStatusMessageByCode($statusCode)
	{

		return self::STATUS_CODES_TO_MESSAGES[$statusCode];

	}

}

?>