<?php
namespace controllers;

class BasicAuth {

	const CREDENTIALS = array(
		'mario' => 'cassar',
		'andrew' => 'green',
		'lug' => 'lug'
		);

	private function _checkCredentials($user, $pass){

		if (!array_key_exists($user, self::CREDENTIALS) 
			|| ($pass !== self::CREDENTIALS[$user])){

			return false;
		}
		return true;
	}

	public function authenticate(){

		$isAuthenticated = false;

		if (isset($_SERVER['PHP_AUTH_USER']) 
			&& isset($_SERVER['PHP_AUTH_PW'])){

			$user = $_SERVER['PHP_AUTH_USER'];
			$pass = $_SERVER['PHP_AUTH_PW'];
			if($this->_checkCredentials($user, $pass)){
				$isAuthenticated = true;
			}
		}
		if(!$isAuthenticated){
			header('WWW-Authenticate: Basic realm="Access to API"');
  			header('HTTP/1.0 401 Unauthorized');
  			die("Not authorized");
		}
	}

}

?>