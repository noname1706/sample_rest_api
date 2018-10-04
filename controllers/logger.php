<?php
namespace controllers;

class Logger {

	const LOG_DIR = 'logs';
	const UNHANDLED_EXCEPTIONS_LOG_FILENAME = 'unhandled_exceptions';
	const LOG_FILENAME_EXTENSION = '.log';

	public static function logUnhandledException($exception)
	{

		$fp = fopen(self::LOG_DIR . '/' . self::UNHANDLED_EXCEPTIONS_LOG_FILENAME 
			. self::LOG_FILENAME_EXTENSION, "a");
		fwrite($fp, $exception->getMessage() . "\n\n");
		fclose($fp);

	}

	public function log($message)
	{

		$fp = fopen(self::LOG_DIR . '/' . self::UNHANDLED_EXCEPTIONS_LOG_FILENAME 
			. self::LOG_FILENAME_EXTENSION, "a");
		fwrite($fp, $message . "\n\n");
		fclose($fp);		
	}
}

?>