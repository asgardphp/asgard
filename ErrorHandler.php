<?php
namespace Coxis\Core;

class ErrorHandler {
	protected static $reservedMemory;
	protected static $errorAtStart;

	public static function initialize() {
		ini_set('log_errors', 0);
		static::$reservedMemory = str_repeat('a', 10240);
		static::$errorAtStart = error_get_last();

		set_error_handler(array('Coxis\Core\ErrorHandler', 'phpErrorHandler'));
		set_exception_handler(array('Coxis\Core\ErrorHandler', 'exceptionHandler'));
		register_shutdown_function(array('Coxis\Core\ErrorHandler', 'shutdownFunction'));
	}

	public static function getErrorAtStart() {
		return static::$errorAtStart;
	}

	public static function shutdownFunction() {
		if(($e=error_get_last()) && $e !== static::$errorAtStart) {
	        $exceptionHandler = set_exception_handler(function() {});
	        restore_exception_handler();
			$exception = new FatalErrorException($e['message'], $e['type'], 0, $e['file'], $e['line']);
			call_user_func_array($exceptionHandler, array($exception));
		}
	}

	public static function getBacktraceFromException($e) {
		$trace = $e->getTrace();

		if($e instanceof FatalErrorException) {
			if(function_exists('xdebug_get_function_stack')) {
				$trace = array_slice(array_reverse(xdebug_get_function_stack()), 4);
				foreach ($trace as $i => $frame) {
					if (!isset($frame['type']))
						$trace[$i]['type'] = '?';
					elseif ($trace[$i]['type'] === 'dynamic')
						$trace[$i]['type'] = '->';
					elseif ($trace[$i]['type'] === 'static')
						$trace[$i]['type'] = '::';

					if (isset($frame['params']) && !isset($frame['args'])) {
						$trace[$i]['args'] = $frame['params'];
						unset($trace[$i]['params']);
					}
				}
				$lastStep = array(
					'line' => $e->getLine(),
					'file' => $e->getFile(),
				);
				array_unshift($trace, $lastStep);
			}
			else
                $trace = array_slice(array_reverse($trace), 1);
		}

		return $trace;
	}

	public static function phpErrorHandler($errno, $errstr, $errfile, $errline) {
		if(static::isLogging() && App::get('config')->get('log_php_errors'))
			static::log(\Psr\Log\LogLevel::NOTICE, 'PHP ('.static::getPHPError($errno).'): '.$errstr, $errfile, $errline);
		throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
	}

	public static function exceptionHandler($e) {
		static::$reservedMemory = null;

		#PSRException with a given severity
		if($e instanceof PSRException) {
			if(static::isLogging())
				$severity = $e->getseverity();
			$msg = $e->getMessage();
		}
		#PHP exception
		elseif($e instanceof \ErrorException) {
			if(static::isLogging())
				$severity = static::getPHPErrorSeverity($e->getCode());
			$msg = 'PHP ('.static::getPHPError($e->getCode()).'): '.$e->getMessage();
		}
		#other exception
		else {
			if(static::isLogging())
				$severity = \Psr\Log\LogLevel::ERROR;
			$msg = get_class($e).': '.$e->getMessage();
		}

		$trace = static::getBacktraceFromException($e);

		if(static::isLogging())
			static::log($severity, $msg, $e->getFile(), $e->getLine(), $trace);
		
		if(PHP_SAPI == 'cli')
			echo static::getCLIErrorResponse($msg, $trace);
		else
			static::getHTTPErrorResponse($msg, $trace)->send();
		exit(1);
	}

	public static function logException($e) {
		if(!static::isLogging())
			return;

		#PSRException with a given severity
		if($e instanceof PSRException) {
			$severity = $e->getseverity();
			$msg = $e->getMessage();
		}
		#PHP exception
		elseif($e instanceof \ErrorException) {
			$severity = static::getPHPErrorSeverity($e->getCode());
			$msg = 'PHP ('.static::getPHPError($e->getCode()).'): '.$e->getMessage();
		}
		#other exception
		else {
			$severity = \Psr\Log\LogLevel::ERROR;
			$msg = get_class($e).': '.$e->getMessage();
		}

		$trace = static::getBacktraceFromException($e);

		static::log($severity, $msg, $e->getFile(), $e->getLine(), $trace);
	}

	public static function log($severity, $message, $file, $line, $trace=null) {
		if(!static::isLogging())
			return;

		$context = array(
			'file' => $file,
			'line' => $line,
			'trace' => $trace,
		);
		static::getLogger()->log($severity, $message, $context);
	}

	public static function isLogging() {
		return App::get('config')->get('log') && !!static::getLogger();
	}

	public static function getLogger() {
		return App::get('logger');
	}

	public static function getPHPErrorSeverity($code) {
		$PHP_ERROR_LEVELS = array(
			E_PARSE => \Psr\Log\LogLevel::ERROR,
			E_ERROR => \Psr\Log\LogLevel::ERROR,
			E_CORE_ERROR => \Psr\Log\LogLevel::ERROR,
			E_COMPILE_ERROR => \Psr\Log\LogLevel::ERROR,
			E_USER_ERROR => \Psr\Log\LogLevel::ERROR,
			E_RECOVERABLE_ERROR => \Psr\Log\LogLevel::ERROR,
			E_WARNING => \Psr\Log\LogLevel::WARNING,
			E_CORE_WARNING => \Psr\Log\LogLevel::WARNING,
			E_COMPILE_WARNING => \Psr\Log\LogLevel::WARNING,
			E_USER_WARNING => \Psr\Log\LogLevel::WARNING,
			E_NOTICE => \Psr\Log\LogLevel::NOTICE,
			E_USER_NOTICE => \Psr\Log\LogLevel::NOTICE,
			E_STRICT => \Psr\Log\LogLevel::NOTICE,
		);
		return $PHP_ERROR_LEVELS[$code];
	}

	public static function getPHPError($code) {
		$errors = array(
			1 => 'E_ERROR',
			2 => 'E_WARNING',
			4 => 'E_PARSE',
			8 => 'E_NOTICE',
			16 => 'E_CORE_ERROR',
			32 => 'E_CORE_WARNING',
			64 => 'E_COMPILE_ERROR',
			128 => 'E_COMPILE_WARNING',
			256 => 'E_USER_ERROR',
			512 => 'E_USER_WARNING',
			1024 => 'E_USER_NOTICE',
			2048 => 'E_STRICT',
			4096 => 'E_RECOVERABLE_ERROR',
			8192 => 'E_DEPRECATED',
			16384 => 'E_USER_DEPRECATED',
		);
		return $errors[$code];
	}

	public static function isFatal($severity) {
		return in_array($severity, App::get('config')->get('fatal_errors'));
	}

	public static function getCLIErrorResponse($msg, $backtrace=null) {
		$result = '';
		if($msg)
			$result .= $msg."\n\n";
		$result .= \Coxis\Utils\Debug::getReport($backtrace);
		echo $result;
	}

	public static function getHTTPErrorResponse($msg, $backtrace=null) {
		$result = '';
		if($msg) {
			$result .= '<b>Message</b><br>'."\n";
			$result .= $msg."<hr>\n";
		}
		$result .= \Coxis\Utils\Debug::getReport($backtrace);
	
		App::get('response')->setCode(500);
		if(App::get('config')->get('debug'))
			return App::get('response')->setHeader('Content-Type', 'text/html')->setContent($result);
		else
			return App::get('response')->setHeader('Content-Type', 'text/html')->setContent('<h1>Error</h1>Oops, something went wrong.');
	}
}
