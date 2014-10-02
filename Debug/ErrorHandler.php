<?php
namespace Asgard\Debug;

/**
 * Error handler.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class ErrorHandler {
	/**
	 * Reserved memory.
	 * @var string
	 */
	protected static $reservedMemory;
	/**
	 * Error at startup.
	 * @var array
	 */
	protected static $errorAtStart;
	/**
	 * Ignore directories.
	 * @var array
	 */
	protected $ignoreDirs = [];
	/**
	 * Log PHP errors flag.
	 * @var boolean
	 */
	protected $logPHPErrors = false;
	/**
	 * Logger
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * Register the PHP error handler.
	 * @return ErrorHandler
	 */
	public static function register() {
		static::$reservedMemory = str_repeat('a', 10240);
		static::$errorAtStart = error_get_last();

		$errorHandler = new static();
		set_error_handler([$errorHandler, 'phpErrorHandler']);
		set_exception_handler([$errorHandler, 'exceptionHandler']);
		register_shutdown_function([$errorHandler, 'shutdownFunction']);
		return $errorHandler;
	}

	/**
	 * Called on shutdown.
	 */
	public function shutdownFunction() {
		if(($e=error_get_last()) && $e !== static::$errorAtStart) {
			while(ob_get_level()) { ob_end_clean(); }
			$exceptionHandler = set_exception_handler(function() {});
			restore_exception_handler();
			$exception = new FatalErrorException($e['message'], $e['type'], 0, $e['file'], $e['line']);
			call_user_func_array($exceptionHandler, [$exception]);
		}
	}

	/**
	 * Ignore PHP errors from a directory.
	 * @param  string $dir
	 * @return ErrorHandler $this
	 */
	public function ignoreDir($dir) {
		$this->ignoreDirs[] = realpath($dir);
		return $this;
	}

	/**
	 * Return backtrace from an exception.
	 * @param  \Exception $e
	 * @return array
	 */
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

	/**
	 * To log PHP errors.
	 * @param boolean $log
	 */
	public function setLogPHPErrors($log) {
		$this->logPHPErrors = $log;
		return $this;
	}

	/**
	 * PHP Error handler.
	 * @param  integer $errno
	 * @param  string  $errstr
	 * @param  string  $errfile
	 * @param  integer $errline
	 * @throws \ErrorException For all PHP errors.
	 */
	public function phpErrorHandler($errno, $errstr, $errfile, $errline) {
		foreach($this->ignoreDirs as $dir) {
			if(strpos($errfile, $dir) === 0)
				return;
		}

		if($this->isLogging() && $this->logPHPErrors)
			$this->log(\Psr\Log\LogLevel::NOTICE, 'PHP ('.static::getPHPError($errno).'): '.$errstr, $errfile, $errline);
		throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
	}

	/**
	 * Exception handler.
	 * @param  \Exception $e
	 * @param  boolean   $kill kill the process
	 */
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

	/**
	 * Log an exception.
	 * @param  \Exception $e
	 */
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

	/**
	 * Log an error.
	 * @param  integer $severity
	 * @param  string  $message
	 * @param  string  $file
	 * @param  integer $line
	 * @param  array   $trace
	 */
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

	/**
	 * Check if is logging.
	 * @return boolean true if logging
	 */
	public function isLogging() {
		return !!$this->logger;
	}

	/**
	 * Set a logger dependency.
	 * @param \Psr\Log\LoggerInterface $logger
	 */
	public function setLogger(\Psr\Log\LoggerInterface $logger) {
		$this->logger = $logger;
		return $this;
	}

	/**
	 * Return error severity from error code.
	 * @param  integer $code
	 * @return integer
	 */
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

	/**
	 * Get PHP error type from code.
	 * @param  integer $code
	 * @return string
	 */
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
