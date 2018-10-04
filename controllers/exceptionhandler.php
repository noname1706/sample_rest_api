<?php
namespace controllers;

class ExceptionHandler {

	/** Contains classes of exceptions that we may throw during 
		route resolving, getting response from the queried resouce, and so on.
		Also it contains corresponding HTTP status codes which will be used
		to form a response to the client.
	*/
	private $_exceptionClasses = array(

		'ClientErrorException' => array(
			
			400 => 'Bad request',
			401 => 'Unauthorized',
			403 => 'Forbidden',
			404 => 'Not found', 
			405 => 'Method not allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			414 => 'URI Too Long',
			
			),
		
		'ServerErrorException' => array(

			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			503 => 'Service Unavailable',
			505 => 'HTTP Version Not Supported',
			
			),

		);
	
	/** Holds current exception received. */
	private $_exception = null;

	public function handleException($exception)
	{
		
		$exceptionClass = getShortClassName(get_class($exception));
		
		/*
		* We should log all the exceptions we do not handle.
		*/
		if (!array_key_exists($exceptionClass, $this->_exceptionClasses)){

			$this->_processUnhandledException($exception);
		
		}

		$headerCodeAndMessage = $this->_getHeaderCodeAndMessageByException($exception);
		$headerCode =$headerCodeAndMessage['headerCode'];
		$message = $headerCodeAndMessage['message'];

		if (!$this->_isValidHeaderCode($headerCode,$exceptionClass) 
				|| !$this->_isValidExceptionMessage($message)){
			
			$this->_processUnhandledException($exception);
		
		}

		$renderer = new Renderer($headerCode, $message, 'views/exceptions/', 'simple_exception');
		$renderer->render();

	}

	/**
	  * Here we will parse the message of the generated exception to 
	  	retrieve the message itself(like: "Not valid language requested", etc) and
	  	status code that will be sent to the client(like: "404", "200", etc). 
 	  *
	  * @param Exception $exception Holds generated exception.
	  * @return array
	  */
	private function _getHeaderCodeAndMessageByException($exception)
	{

		$message = $exception->getMessage();

		if (strpos($message, "##") === false){
			
			return array('headerCode' => false, 'message' => false);
		
		} else {
			
			$mArray = explode("##", $message);
			$messageText = trim($mArray[0]);
			$headerCode = trim($mArray[1]);
			return array('headerCode' => $headerCode, 'message' => $messageText);
		
		}

	}

	/**
	  * If we have an unhandled exception let's log it and send corresponding
	   message the the client.  
 	  *
	  * @param Exception $exception Holds generated exception.
	  * @return void
	  */
	private function _processUnhandledException($exception)
	{

		\controllers\Logger::logUnhandledException($exception);
		$renderer = new Renderer(500, 'Unhandled exception', 'views/exceptions/', 'simple_exception');
		$renderer->render();
	
	}

	/**
	  * Will validate status code present in the received exception message.s   
 	  *
	  * @param integer $headerCode Holds status code parsed from the received exception message.
	  * @param string $exceptionClass Holds the "class" of the received exception(like: "ClientErrorException").

	  * @return bool
	  */
	private function _isValidHeaderCode($headerCode, $exceptionClass)
	{

		if (!array_key_exists(intval($headerCode), 
				$this->_exceptionClasses[$exceptionClass])){
			return false;
		
		}

		return true;
	
	}

	/**
	  * will validate message for allowed characters.
 	  *
	  * @param string $message Holds text message parsed from the message of the exception received.

	  * @return bool
	  */
	private function _isValidExceptionMessage($message)
	{

		$pattern = "#^[A-Za-z0-9\/\._=&!\s]{" . strlen($message) ."}$#";
		$matches = array();
		preg_match($pattern, $message, $matches);
		
		if (empty($matches)){
			
			return false;
		
		}
		
		return true;
	}

}

?>