<?php
namespace controllers;

class User {

	/** Valid languages for "User" controller. */
	const VALID_LANGUAGES = array('en', 'ua', 'ru');

	/**Currently requested language. */
	private $_language = null;

	/**The name of the model's class that will be used in the current request. */
	private $_modelClassName = null;

	/**The name of the current controller without a namespace. */
	private $_shortClassName = null;

	public function __construct($route, $modelClassName = false)
	{

		$this->_initController($route);

		if ($modelClassName === false){

			$this->_modelClassName = "\\models\\" . $this->_shortClassName;	
		
		} else {

			$this->_modelClassName = $modelClassName;

		}
		
	}

	/**
	  * Sends(using "Renderer" class) all users to the client. 
 	  *
	  * @param string $route Holds requested route.
	  * @return void
	  */
	public function actionAll($route)
	{

		$page = $this->_getPage($route);
		$model = new $this->_modelClassName();

		$responseArray = $model->getAll($page);
		$response = $responseArray['response'];
		$lastModified = $responseArray['lastModified'];

		if ($response === false){

			throw new \exceptions\ClientErrorException('No users present ## 404');

		}

		$renderer = new Renderer(200, $response, 'views/', 'simple_table');
		$renderer->setResponseLastModified($lastModified);
		$renderer->render();
	}


	/**
	  * Sends(using "Renderer" class) one user found by ID to the client. 
 	  *
	  * @param string $route Holds requested route.
	  * @return void
	  */
	public function actionGetOneById($route)
	{

		/** Retrieve page number from the $route. */
		$page = $this->_getPage($route);
		$controllerClassNameLength = strlen($this->_shortClassName);
		$firstPart = strstr($route, lcfirst($this->_shortClassName));
		$secondPart = substr($firstPart, $controllerClassNameLength, 
			strlen($firstPart)-$controllerClassNameLength);

		$matches = array();
		/** Retrieve user "id" here. */
		preg_match("#^\/\d{1,}\/{0,1}#", $secondPart, $matches);
		$id = intval(trim($matches[0], "/"));

		$model = new $this->_modelClassName();

		$response = $model->getOneById($id, $page);

		if ($response === false){

			throw new \exceptions\ClientErrorException('User not found ## 404');

		}

		/** Send decorated response to the client. */
		$renderer = new Renderer(200, $response, 'views/', 'simple_table');
		$renderer->setResponseLastModified($response[$id]['lastModified']);
		$renderer->render();
	}

	/**
	  * Retrieves page number from the $route.
	  *
	  * @param string $route Holds requested route.
	  * @return integer
	  */
	private function _getPage($route)
	{

		$pageSection = strstr($route, "/page="); 
		
		if ($pageSection === false){
			
			return 1;
		
		}
		
		return intval(substr($pageSection, 6, strlen($pageSection) - 6));

	}

	/**
	  * Initializes controller.   
 	  *
	  * @param string $route Holds requested route.
	  * @return void
	  */
	private function _initController($route)
	{

		$this->_shortClassName = getShortClassName(get_class());
		$this->_setLanguageFromRoute($route);
		
	}

	/**
	  * Sets the requested language from the $route if the language is valid.  .   
 	  *
	  * @param string $route Holds requested route.
	  * @return void
	  */
	private function _setLanguageFromRoute($route)
	{

		$language = substr($route, 0, 2);

		/** If the language requested is not supported error page with appropriate header
			will be sent to the client.
		 */
		if (!$this->_validateLanguage($language)){
			/** Along with an error message we provide a status code to indicate a type
				of the error occured. This("404") status code will be used later when we will send the response to the client.
			 */
			throw new \exceptions\ClientErrorException('Not supported language parameter provided! ## 404');
		
		}
		/** Let's set the language requested if it is valid. */
		$this->_language = $language;
	
	}

	private function _validateLanguage($language)
	{

		if (!in_array($language, self::VALID_LANGUAGES)){
			
			return false;
		}
		
		return true;
	}

}

?>