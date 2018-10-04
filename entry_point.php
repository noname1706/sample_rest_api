<?php
ini_set('display_errors', 1);
define("ENTRY_POINT_NAME", 'entry_point.php');
require 'init.php';

$auth = new \controllers\BasicAuth();
$auth->authenticate();

/** Display entry page if no resourse specified. */
if (trim($_SERVER['REQUEST_URI'], "/") === ENTRY_POINT_NAME){
	
	require 'entry_form.php';
	exit;

}

try {
	
	$router = new controllers\Router();
	$router->findTheHandler($_SERVER['REQUEST_URI']);
	$route = $router->getCurrentRoute();
	$handler = $router->getCurrentHandler();
	
	if (is_null($handler)){
		throw new \exceptions\ClientErrorException('Route not found ## 404');
	}
	$handlerArray = explode("#", $handler);

	$controllerName = "controllers\\" . $handlerArray[0];
	$actionName = 'action' . ucfirst($handlerArray[1]);

	$controller = new $controllerName($route);
	$controller->$actionName($route);

} catch(\Exception $e){

	$exceptionHandler = new controllers\exceptionHandler();
	$exceptionHandler->handleException($e);

}

?>