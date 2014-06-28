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
		\Asgard\Debug\ErrorHandler::register();
		$this->setExpectedException('ErrorException');

		$a = $b;
	}

	public function testLogging() {
		$errorHandler = new \Asgard\Debug\ErrorHandler();
		$logger = $this->getMock('Log', ['log']);
		$logger->expects($this->once())->method('log')->with('error', 'Exception: ', $this->callback(function($a) {
			return isset($a['file']) && isset($a['line']) && isset($a['trace']);
		}));
		$errorHandler->setLogger($logger);
		$errorHandler->logException(new \Exception);

		$logger = $this->getMock('Log', ['log']);
		$logger->expects($this->once())->method('log')->with('error', 'A message', $this->callback([$this, 'checkLog']));
		$errorHandler->setLogger($logger);
		$errorHandler->logException(new \Asgard\Debug\PSRException('A message'));

		set_error_handler([$errorHandler, 'phpErrorHandler']);
		try {
			echo $a;
			$this->assertTrue(false, 'Should not reach this line.');
		} catch(\ErrorException $e) {
			$logger = $this->getMock('Log', ['log']);
			$logger->expects($this->once())->method('log')->with('notice', 'PHP (E_NOTICE): Undefined variable: a', $this->callback([$this, 'checkLog']));
			$errorHandler->setLogger($logger);
			$errorHandler->logException($e);
		}
	}

	public function testExceptionHandler() {
		$errorHandler = new \Asgard\Debug\ErrorHandler;

		set_error_handler([$errorHandler, 'phpErrorHandler']);
		try {
			echo $a;
			$this->assertTrue(false, 'Should not reach this line.');
		} catch(\ErrorException $e) {
			$errorHandler->exceptionHandler($e, false);
		}
		$this->hasOutput();
	}

	public function testIgnoreDir() {
		$errorHandler = new \Asgard\Debug\ErrorHandler;
		$errorHandler->ignoreDir(__DIR__.'/fixtures');
		set_error_handler([$errorHandler, 'phpErrorHandler']);
		include __DIR__.'/fixtures/error.php';
	}

	public function checkLog($a) {
		return isset($a['file']) && isset($a['line']) && isset($a['trace']);
	}
}

class Log {
	public function log($level, $message, array $context = []) {
	}
}

class Debugger {
	public function report($app, $msg, $backtrace=null) {
	}
}