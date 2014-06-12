<?php
namespace Asgard\Http;

class HttpKernel {
	protected $app;
	protected $loaded = false;
	protected $requests = [];
	protected $start;
	protected $end;

	public function __construct($app) {
		$this->app = $app;
	}

	public function start($start) {
		$this->start = $start;
	}

	public function end($end) {
		$this->end = $end;
	}

	public function run() {
		$request = \Asgard\Http\Request::instance();
		$request->isInitial = true;

		$response = $this->process($request);

		$this->app['hooks']->trigger('Asgard.Http.Output', [$response, $request]);
		return $response;
	}

	private function setRequest($request) {
		$this->app['request'] = $request;
		\Asgard\Http\Request::setInstance($request);
	}

	public function process(Request $request, $catch=true) {
		$this->setRequest($request);
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

				$this->app['hooks']->trigger('Asgard.Http.Exception.'.get_class($e), [$e, &$response, $request]);
				if($response === null)
					$response = $this->getExceptionResponse($e);
			}
		}

		try {
			if($this->end !== null)
				include $this->end;
			$this->app['hooks']->trigger('Asgard.Http.End', [$response]);
		} catch(\Exception $e) {
			$this->app['errorHandler']->logException($e);
		}

		array_pop($this->requests);
		if(isset($this->requests[count($this->requests)-1])) {
			$this->setRequest($this->requests[count($this->requests)-1]);
		}

		return $response;
	}

	public function getLastRequest() {
		if(!isset($this->requests[count($this->requests)-1]))
			return;
		return $this->requests[count($this->requests)-1];
	}

	protected function processRaw(Request $request, $catch=true) {
		$resolver = $this->app['resolver'];
		$resolver->sortRoutes();

		if($this->start !== null) {
			$app = $this->app;
			include $this->start;
		}
		if($response = $this->app['hooks']->trigger('Asgard.Http.Start', [$request]))
			return $response;

		$callback = $resolver->getCallback($request);
		if($callback === null)
			throw new Exceptions\NotFoundException;
		$arguments = $resolver->getArguments($request);

		$response = call_user_func_array($callback, array_merge($arguments, [$this->app, $request]));

		return $response;
	}

	protected function getExceptionResponse($e) {
		while(ob_get_length())
			ob_end_clean();
		$this->app['errorHandler']->exceptionHandler($e, false);

		$trace = $this->app['errorHandler']->getBacktraceFromException($e);
		
		if($e instanceof PSRException)
			$msg = $e->getMessage();
		elseif($e instanceof \ErrorException)
			$msg = 'PHP ('.$this->app['errorHandler']->getPHPError($e->getCode()).'): '.$e->getMessage();
		else
			$msg = get_class($e).': '.$e->getMessage();

		$result = '<b>Message</b><br>'."\n"
			. $msg."<hr>\n"
			. \Asgard\Debug\Debug::getReport($trace);
	
		$response = new \Asgard\Http\Response(500);
		if($this->app['config']['debug'])
			return $response->setHeader('Content-Type', 'text/html')->setContent($result);
		else
			return $response->setHeader('Content-Type', 'text/html')->setContent($this->app['translator']->trans('<h1>Error</h1>Oops, something went wrong.'));
	}
}
