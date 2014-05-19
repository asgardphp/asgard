<?php
namespace Asgard\Http;

class HttpKernel {
	protected $app;
	protected $loaded = false;
	protected $requests = array();

	public function __construct(\Asgard\Core\App $app) {
		$this->app = $app;
	}

	public function run() {
		$request = \Asgard\Http\Request::createFromGlobals();
		$request->isInitial = true;
		$this->app->set('request', $request);

		$response = $this->process($request);

		$this->app->get('hook')->trigger('output', array($response, $request));
		return $response;
	}

	public function process(Request $request, $catch=true) {
		$this->app['request'] = $request;
		$this->requests[] = $request;

		if(!$catch)
			$response = $this->processRaw($request);
		else {
			try {
				$response = $this->processRaw($request);
			} catch(\Exception $e) {
				if($e instanceof ControllerException) {
					$response = $e->getResponse();
					$severity = $e->getSeverity();
					$trace = $this->app['errorHandler']->getBacktraceFromException($e);
					$this->app['errorHandler']->log($severity, $e->getMessage(), $e->getFile(), $e->getLine(), $trace);
				}
				else {
					$response = null;
					$this->app['errorHandler']->logException($e);
				}

				$this->app['hook']->trigger('exception_'.get_class($e), array($e, $response, $request));
				if($response === null)
					$response = $this->getExceptionResponse($e);
			}
		}

		try {
			$this->app['hook']->trigger('frontcontroller_end', array($response));
		} catch(\Exception $e) {
			$this->app['errorHandler']->logException($e);
		}

		array_pop($this->requests);
		if(isset($this->requests[count($this->requests)-1]))
			$this->app['request'] = $this->requests[count($this->requests)-1];

		return $response;
	}

	public function getLastRequest() {
		if(!isset($this->requests[count($this->requests)-1]))
			return;
		return $this->requests[count($this->requests)-1];
	}

	protected function processRaw(Request $request, $catch=true) {
		$resolver = $this->app['resolver'];
		$resolver->setRequest($request);

		if(defined('_DIR_') && file_exists(_DIR_.'app/start.php'))
			include _DIR_.'app/start.php';
		$this->app['hook']->trigger('start', array($request));

		$callback = $resolver->getCallback($request);
		if($callback === null)
			throw new Exceptions\NotFoundException;
		$arguments = $resolver->getArguments($request);

		$response = call_user_func_array($callback, array_merge($arguments, array($this->app, $request)));

		return $response;
	}

	protected function getExceptionResponse($e) {
		$trace = $this->app['errorHandler']->getBacktraceFromException($e);
		$msg = $e->getMessage() ? get_class($e).': '.$e->getMessage():'Uncaught exception: '.get_class($e);
		return $this->app['errorHandler']->getHTTPErrorResponse($msg, $trace);
	}
}
