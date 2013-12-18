<?php
namespace Coxis\Core;

class HttpKernel {
	public static function run() {
		App::load();
		static::process(\Request::inst(), true)->send();
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
				if($e instanceof \Coxis\Core\ControllerException) {
					$severity = $e->getSeverity();
					$msg = 'Controller: '.$e->getMessage();
					$trace = ErrorHandler::getBacktraceFromException($e);
					ErrorHandler::log($severity, $msg, $e->getFile(), $e->getLine(), $trace);
				}
				else {
					ErrorHandler::logException($e);
				}

				$response = \Coxis\Core\Hook\Hook::trigger('exception_'.get_class($e), array($e));
				if($response === null)
					$response = static::getExceptionResponse($e);
			}
		}

		try {
			\Coxis\Core\App::get('hook')->trigger('frontcontroller_end', array($response));
		} catch(\Exception $e) {
			ErrorHandler::logException($e);
		}

		App::instance()->request = $previousRequest;

		return $response;
	}

	protected static function processRaw($request, $catch=true) {
		if(file_exists(_DIR_.'app/start.php'))
			include _DIR_.'app/start.php';
		\Coxis\Core\App::get('hook')->trigger('start');

		$resolver = \Resolver::inst();

		$callback = $resolver->getCallback($request);
		if($callback === null)
			throw new \Coxis\Core\Exceptions\NotFoundException;
		$arguments = $resolver->getArguments($request);

		$response = call_user_func_array($callback, array_merge($arguments, array($request)));

		return $response;
	}

	protected static function getExceptionResponse($e) {
		$trace = ErrorHandler::getBacktraceFromException($e);
		return ErrorHandler::getHTTPErrorResponse($e->getMessage(), $trace);
	}
}
