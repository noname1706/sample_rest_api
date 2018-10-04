<?php
namespace controllers;

class Router {

	/** Holds PCRE for paging part of the route. */
	const PAGING_PART = "(\/page=\d{1,10}){0,1}";
	/** Holds PCRE for language part of the route. */
	const LANG_PART = "[a-z]{2}";
	/** Valid language codes. */
	const VALID_LANGUAGES = array('en', 'ru');
	/** Max allowed length of the URI. */
	const MAX_URI_LENGTH = 10000;
	
	/** Current request method: GET, POST, etc */
	private $_currentRequestMethod = null;
	/** The request handler determined by the current route. */
	private $_currentHandler = null;
	/** The route received. */
	private $_currentRoute = null;
	/** The URL asked by the client. */
	private $_requestUrl = null;
	/** The controller of the current handler. */
	private $_controller = null;
	/** The action of the current handler. */
	private $_action = null;
	/** Parameters of the action of the current handler. */
	private $_actionParameters = null;
	/** PCRE for each request method(GET, POST, etc), PCREs of each method's routes and
	corresponding handlers.
	*/
	private $_routingTable = array(

	  "GET" => array(
		"^{lang}\/user\/all{paging}$" => "User#all",
		"^{lang}\/user\/\d{1,10}{paging}$" => "User#getOneById"
			),
	  "POST" => array(),
		);

	public function getCurrentRoute()
	{

		return $this->_currentRoute;
	}

	public function getCurrentHandler()
	{

		return $this->_currentHandler;
	}

	public function getCurrentRequestMethod()
	{

		return $this->_currentRequestMethod;
	}

	/**
	  * Finds the corresponding handler for the current route.
	  Throws exceptions if the requested URI is not valid. 
	  *
	  * @return void
	  */
	public function findTheHandler($uri)
	{

		if (!$this->_validateRequestURI($uri)){
			/** we need the message text itself and the status code that will be used in 
			"Renderer" to send appropriate response to the client.
			*/
			throw new \exceptions\ClientErrorException('Request URI is not valid! ## 404');
		
		}

		$uri = trim($uri);
		$requestMethod = $_SERVER['REQUEST_METHOD'];
		$this->_currentRequestMethod = $requestMethod;
		$scriptName = $_SERVER['SCRIPT_NAME'];
		$requestedRoute = trim(substr($uri, strlen($scriptName), 
			strlen($uri) - strlen($scriptName)), "/");
		
		$methodRoutesToHandlers = $this->_routingTable[$requestMethod];
		$methodRoutes = array_keys($methodRoutesToHandlers);
		$handlers = array_values($methodRoutesToHandlers);
		
		foreach ($methodRoutes as $key => $route) {
			$methodRoutes[$key] = str_replace('{lang}', self::LANG_PART, $route); 
		}
		foreach ($methodRoutes as $key => $route) {
			$methodRoutes[$key] = str_replace('{paging}', self::PAGING_PART, $route);
		}
		$handlers = array_combine($methodRoutes, $handlers);
		
		$currentHandler = null;
		
		foreach($methodRoutes as $tableEntry){
			
			$pattern = '#' . $tableEntry . '#';
			
			if (preg_match_all($pattern, $requestedRoute, $matches)){

				$currentHandler = $handlers[$tableEntry]; 
				break;
			
			}
		
		}

		$this->_currentHandler = $currentHandler;
		
		if (!empty($matches[0][0])){
			
			$this->_currentRoute = $matches[0][0];	
		
		}

	}

	public function getController()
	{

		return $this->_controller;
	}

	public function getAction()
	{

		return $this->_action;
	}

	public function getActionParameters()
	{

		return $this->_actionParameters;
	}

	private function _validateRequestURI($uri)
	{

		$uriLen = strlen($uri);
		
		if ($uriLen > self::MAX_URI_LENGTH){
			
			return false;
		
		}
		
		$pattern = "#^[A-Za-z0-9\/\._=&]{" . $uriLen ."}$#";
		$matches = array();
		preg_match($pattern, $uri, $matches);
		
		if (empty($matches)){
			
			return false;
		
		}

		return true;
	}
}

?>