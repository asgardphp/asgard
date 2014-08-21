<?php
namespace Asgard\Debug;

class ErrorHandler {
	protected static $reservedMemory;
	protected static $errorAtStart;
	protected $ignoreDirs = [];
	protected $logPHPErrors = false;
	protected $logger;

	public static function register() {
		static::$reservedMemory = str_repeat('a', 10240);
		static::$errorAtStart = error_get_last();

		$errorHandler = new static();
		set_error_handler([$errorHandler, 'phpErrorHandler']);
		set_exception_handler([$errorHandler, 'exceptionHandler']);
		register_shutdown_function([$errorHandler, 'shutdownFunction']);
		return $errorHandler;
	}

	public function shutdownFunction() {
		if(($e=error_get_last()) && $e !== static::$errorAtStart) {
			while(ob_get_level()) { ob_end_clean(); }
	        $exceptionHandler = set_exception_handler(function() {});
	        restore_exception_handler();
			$exception = new FatalErrorException($e['message'], $e['type'], 0, $e['file'], $e['line']);
			call_user_func_array($exceptionHandler, [$exception]);
		}
	}

	public function ignoreDir($dir) {
		$this->ignoreDirs[] = realpath($dir);
		return $this;
	}

	public function getBacktraceFromException(\Exception $e) {
		$trace = $e->getTrace();

		if($e instanceof FatalErrorException) {
			#Credit to Symfony
			if(function_exists('xdebug_get_function_stack')) {
				$trace = array_slice(array_reverse(xdebug_get_function_stack()), 4);
				foreach($trace as $i => $frame) {
					if(!isset($frame['type']))
						$trace[$i]['type'] = '?';
					elseif($trace[$i]['type'] === 'dynamic')
						$trace[$i]['type'] = '->';
					elseif($trace[$i]['type'] === 'static')
						$trace[$i]['type'] = '::';

					if(isset($frame['params']) && !isset($frame['args'])) {
						$trace[$i]['args'] = $frame['params'];
						unset($trace[$i]['params']);
					}
				}
				$lastStep = [
					'line' => $e->getLine(),
					'file' => $e->getFile(),
				];
				array_unshift($trace, $lastStep);
			}
			else
				$trace = array_slice(array_reverse($trace), 1);
		}

		return $trace;
	}

	public function setLogPHPErrors($log) {
		$this->logPHPErrors = $log;
		return $this;
	}

	public function phpErrorHandler($errno, $errstr, $errfile, $errline) {
		foreach($this->ignoreDirs as $dir) {
			if(strpos($errfile, $dir) === 0)
				return;
		}

		if($this->isLogging() && $this->logPHPErrors)
			$this->log(\Psr\Log\LogLevel::NOTICE, 'PHP ('.static::getPHPError($errno).'): '.$errstr, $errfile, $errline);
		throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
	}

	public function exceptionHandler(\Exception $e, $kill=true) {
		static::$reservedMemory = null;

		$this->logException($e);

		if($kill) {
			$trace = $this->getBacktraceFromException($e);
			
			if($e instanceof PSRException)
				$msg = $e->getMessage();
			elseif($e instanceof \ErrorException)
				$msg = 'PHP ('.static::getPHPError($e->getCode()).'): '.$e->getMessage();
			else
				$msg = get_class($e).': '.$e->getMessage();

			$result = '';
			if($msg)
				$result .= $msg."\n\n";
			$result .= Debug::getReport($trace);
			echo $result;
			exit(1);
		}
	}

	public function logException(\Exception $e) {
		if(!$this->isLogging())
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

		$trace = $this->getBacktraceFromException($e);

		$this->log($severity, $msg, $e->getFile(), $e->getLine(), $trace);
	}

	public function log($severity, $message, $file, $line, $trace=null) {
		if(!$this->isLogging())
			return;

		$context = [
			'file' => $file,
			'line' => $line,
			'trace' => $trace,
		];
		$this->logger->log($severity, $message, $context);
	}

	public function isLogging() {
		return !!$this->logger;
	}

	public function setLogger($logger) {
		$this->logger = $logger;
		return $this;
	}

	public static function getPHPErrorSeverity($code) {
		$PHP_ERROR_LEVELS = [
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
		];
		return $PHP_ERROR_LEVELS[$code];
	}

	public static function getPHPError($code) {
		$errors = [
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
		];
		return $errors[$code];
	}
}
