<?php
namespace Asgard\Debug\Tests;

class ErrorHandlerTest extends \PHPUnit_Framework_TestCase {
	public function testGetBacktraceFromException() {
		$errorHandler = new \Asgard\Debug\ErrorHandler;
		try {
			throw new \Exception;
		} catch(\Exception $e) {
			$trace = $errorHandler->getBacktraceFromException($e);
			$this->assertTrue(is_array($trace));
		}
	}

	public function testPHPError() {
		\Asgard\Debug\ErrorHandler::initialize();
		$this->setExpectedException('ErrorException');

		$a = $b;
	}

	public function testLogging() {
		$errorHandler = new \Asgard\Debug\ErrorHandler();
		$logger = $this->getMock('Asgard\Core\Tests\Log', array('log'));
		$logger->expects($this->once())->method('log')->with('error', 'Exception: ', $this->callback(function($a) {
			return isset($a['file']) && isset($a['line']) && isset($a['trace']);
		}));
		$errorHandler->setLogger($logger);
		$errorHandler->logException(new \Exception);

		$logger = $this->getMock('Asgard\Core\Tests\Log', array('log'));
		$logger->expects($this->once())->method('log')->with('error', 'A message', $this->callback(array($this, 'checkLog')));
		$errorHandler->setLogger($logger);
		$errorHandler->logException(new \Asgard\Debug\PSRException('A message'));

		set_error_handler(array($errorHandler, 'phpErrorHandler'));
		try {
			echo $a;
		} catch(\ErrorException $e) {
			$logger = $this->getMock('Asgard\Core\Tests\Log', array('log'));
			$logger->expects($this->once())->method('log')->with('notice', 'PHP (E_NOTICE): Undefined variable: a', $this->callback(array($this, 'checkLog')));
			$errorHandler->setLogger($logger);
			$errorHandler->logException($e);
		}
	}

	public function testExceptionHandler() {
		$errorHandler = new \Asgard\Debug\ErrorHandler();

		set_error_handler(array($errorHandler, 'phpErrorHandler'));
		try {
			$a;
		} catch(\ErrorException $e) {
			$errorHandler->exceptionHandler($e, false);
		}
		$this->hasOutput();
	}

	public function checkLog($a) {
		return isset($a['file']) && isset($a['line']) && isset($a['trace']);
	}
}

class Log {
	public function log($level, $message, array $context = array()) {
	}
}

class Debugger {
	public function report($app, $msg, $backtrace=null) {
	}
}