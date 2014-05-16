<?php
namespace Asgard\Http;

class HttpKernel {
	public static function run() {
		\Asgard\Core\App::loadDefaultApp(true);
		\Asgard\Utils\Profiler::checkpoint('after load');
		$request = \Asgard\Core\App::get('request');
		$request->isInitial = true;
		static::process($request, true)->send();
	}

	public static function process(\Asgard\Http\Request $request, $catch=false) {
		$previousRequest = \Asgard\Core\App::get('request');
		\Asgard\Core\App::instance()->request = $request;

		if(!$catch)
			$response = static::processRaw($request);
		else {
			try {
				$response = static::processRaw($request);
			} catch(\Exception $e) {
				if($e instanceof \Asgard\Http\ControllerException) {
					$severity = $e->getSeverity();
					$trace = \Asgard\Core\ErrorHandler::getBacktraceFromException($e);
					\Asgard\Core\ErrorHandler::log($severity, $e->getMessage(), $e->getFile(), $e->getLine(), $trace);
				}
				else
					\Asgard\Core\ErrorHandler::logException($e);

				$response = \Asgard\Core\App::get('hook')->trigger('exception_'.get_class($e), array($e));
				if($response === null)
					$response = static::getExceptionResponse($e);
			}
		}

		try {
			\Asgard\Core\App::get('hook')->trigger('frontcontroller_end', array($response));
		} catch(\Exception $e) {
			\Asgard\Core\ErrorHandler::logException($e);
		}

		\Asgard\Core\App::instance()->request = $previousRequest;

		\Asgard\Utils\Profiler::report();
		return $response;
	}

	protected static function processRaw(\Asgard\Http\Request $request, $catch=true) {
		if(file_exists(_DIR_.'app/start.php'))
			include _DIR_.'app/start.php';
		\Asgard\Core\App::get('hook')->trigger('start');

		$resolver = \Asgard\Core\App::get('resolver');

		$callback = $resolver->getCallback($request);
		if($callback === null)
			throw new Exceptions\NotFoundException;
		$arguments = $resolver->getArguments($request);

		\Asgard\Utils\Profiler::checkpoint('before controller');
		$response = call_user_func_array($callback, array_merge($arguments, array($request)));
		\Asgard\Utils\Profiler::checkpoint('after controller');

		return $response;
	}

	protected static function getExceptionResponse($e) {
		$trace = \Asgard\Core\ErrorHandler::getBacktraceFromException($e);
		$msg = $e->getMessage() ? get_class($e).': '.$e->getMessage():'Uncaught exception: '.get_class($e);
		return \Asgard\Core\ErrorHandler::getHTTPErrorResponse($msg, $trace);
	}
}
