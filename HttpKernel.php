<?php
namespace Coxis\Core;

class HttpKernel {
	public static function run() {
		Coxis::load();
		static::process(\Request::inst(), true)->send();
	}

	public static function process($request, $catch=false) {
		if(!$catch) {
			$response = static::processRaw($request);
		}
		else {
			try {
				$response = static::processRaw($request);
			} catch(\Exception $e) {
				if($e instanceof \Coxis\Core\ControllerException) {
					$severity = $e->getSeverity();
					$msg = 'Controller: '.$e->getMessage();
					$trace = ErrorHandler::getBacktraceFromException($e);
					ErrorHandler::log($severity, $msg, $e->getFile(), $e->getLine(), $trace);
				}
				else {
					ErrorHandler::logException($e);
				}

				$response = \Hook::trigger('exception_'.get_class($e), array($e));
				if($response === null)
					$response = static::getExceptionResponse($e);
			}
		}

		try {
			\Coxis\Core\Context::get('hook')->trigger('frontcontroller_end', array($response));
		} catch(\Exception $e) {
			ErrorHandler::logException($e);
		}

		return $response;
	}

	public static function processRaw($request, $catch=true) {
		$resolver = \Resolver::inst();

		$controller = $resolver->getController($request);
		if($controller === null)
			throw new \Coxis\Core\Exceptions\NotFoundException;
		$arguments = $resolver->getArguments($request);

		$response = call_user_func_array($controller, $arguments);

		return $response;
	}

	public static function getExceptionResponse($e) {
		$trace = ErrorHandler::getBacktraceFromException($e);
		return ErrorHandler::getHTTPErrorResponse($e->getMessage(), $trace);
	}
}
