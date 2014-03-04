<?php
namespace Asgard\Core;

class HttpKernel {
	public static function run() {
		App::loadDefaultApp();
		$request = App::get('request');
		$request->isInitial = true;
		static::process($request, true)->send();
	}

	public static function process($request, $catch=false) {
		$previousRequest = App::instance()->request;
		App::instance()->request = $request;

		if(!$catch) {
			$response = static::processRaw($request);
		}
		else {
			try {
				$response = static::processRaw($request);
			} catch(\Exception $e) {
				if($e instanceof \Asgard\Core\ControllerException) {
					$severity = $e->getSeverity();
					$trace = ErrorHandler::getBacktraceFromException($e);
					ErrorHandler::log($severity, $e->getMessage(), $e->getFile(), $e->getLine(), $trace);
				}
				else {
					ErrorHandler::logException($e);
				}

				$response = \Asgard\Core\App::get('hook')->trigger('exception_'.get_class($e), array($e));
				if($response === null)
					$response = static::getExceptionResponse($e);
			}
		}

		try {
			\Asgard\Core\App::get('hook')->trigger('frontcontroller_end', array($response));
		} catch(\Exception $e) {
			ErrorHandler::logException($e);
		}

		App::instance()->request = $previousRequest;

		return $response;
	}

	protected static function processRaw($request, $catch=true) {
		if(file_exists(_DIR_.'app/start.php'))
			include _DIR_.'app/start.php';
		\Asgard\Core\App::get('hook')->trigger('start');

		$resolver = \Asgard\Core\App::get('resolver');

		$callback = $resolver->getCallback($request);
		if($callback === null)
			throw new \Asgard\Core\Exceptions\NotFoundException;
		$arguments = $resolver->getArguments($request);

		$response = call_user_func_array($callback, array_merge($arguments, array($request)));

		return $response;
	}

	protected static function getExceptionResponse($e) {
		$trace = ErrorHandler::getBacktraceFromException($e);
		$msg = $e->getMessage() ? get_class($e).': '.$e->getMessage():'Uncaught exception: '.get_class($e);
		return ErrorHandler::getHTTPErrorResponse($msg, $trace);
	}
}
