<?php
namespace Asgard\Http;

/**
 * HTTP Kernel.
 */
class HttpKernel {
	use \Asgard\Container\ContainerAwareTrait;

	/**
	 * Variable to check if the kernel was already loaded.
	 * @var boolean
	 */
	protected $loaded = false;
	/**
	 * Nested requests.
	 * @var array
	 */
	protected $requests = [];
	/**
	 * File to be included at startup.
	 * @var string
	 */
	protected $start;
	/**
	 * File to be included on shutdown.
	 * @var string
	 */
	protected $end;
	/**
	 * Template path solvers.
	 * @var array
	 */
	protected $templatePathSolvers = [];
	/**
	 * Controller filters.
	 * @var array
	 */
	protected $filters = [];
	/**
	 * Controller before filters.
	 * @var array
	 */
	protected $beforeFilters = [];
	/**
	 * Controller after filters.
	 * @var array
	 */
	protected $afterFilters = [];

	/**
	 * Constructor.
	 * @param \Asgard\Container\Container $container
	 */
	public function __construct(\Asgard\Container\Container $container) {
		$this->container = $container;
	}

	/**
	 * Set the start file.
	 * @param  string $start
	 */
	public function start($start) {
		$this->start = $start;
	}

	/**
	 * Set the end file.
	 * @param  string $end
	 */
	public function end($end) {
		$this->end = $end;
	}

	/**
	 * Run the kernel and return a response.
	 * @return Response
	 */
	public function run() {
		$request = Request::singleton();
		$request->isInitial = true;

		$response = $this->process($request);

		$this->container['hooks']->trigger('Asgard.Http.Output', [$response, $request]);
		return $response;
	}

	/**
	 * Set the HTTP request.
	 * @param Request $request
	 */
	private function setRequest(Request $request) {
		$this->container['request'] = $request;
		Request::setInstance($request);
	}

	/**
	 * Process the request.
	 * @param  Request $request
	 * @param  boolean $catch   true to catch exceptions.
	 * @return Response
	 */
	public function process(Request $request, $catch=true) {
		#Credit to Symfony
		$this->setRequest($request);
		$this->requests[] = $request;

		if(!$catch) {
			$response = $this->processRaw($request);
			if(!$response instanceof Response)
				$response = (new Response())->setContent($response);
		}
		else {
			try {
				$response = $this->processRaw($request);
				if(!$response instanceof Response)
					$response = (new Response())->setContent($response);
			} catch(\Exception $e) {
				if($e instanceof ControllerException) {
					$response = $e->getResponse();
					$severity = $e->getSeverity();
					$trace = $this->container['errorHandler']->getBacktraceFromException($e);
					$this->container['errorHandler']->log($severity, $e->getMessage(), $e->getFile(), $e->getLine(), $trace);
				}
				else {
					$response = null;
					$this->container['errorHandler']->logException($e);
				}

				$this->container['hooks']->trigger('Asgard.Http.Exception.'.get_class($e), [$e, &$response, $request]);
				if($response === null)
					$response = $this->getExceptionResponse($e);
			}
		}

		try {
			if($this->end !== null)
				include $this->end;
			$this->container['hooks']->trigger('Asgard.Http.End', [$response]);
		} catch(\Exception $e) {
			$this->container['errorHandler']->logException($e);
		}

		array_pop($this->requests);
		if(isset($this->requests[count($this->requests)-1]))
			$this->setRequest($this->requests[count($this->requests)-1]);

		return $response;
	}

	/**
	 * Get the last given request.
	 * @return Request|null
	 */
	public function getLastRequest() {
		if(!isset($this->requests[count($this->requests)-1]))
			return;
		return $this->requests[count($this->requests)-1];
	}

	/**
	 * Return a raw response.
	 * @param  Request $request
	 * @param  boolean $catch   true to catch exceptions.
	 * @throws Exceptions\NotFoundException If route not found.
	 * @return mixed
	 */
	protected function processRaw(Request $request, $catch=true) {
		$resolver = $this->container['resolver'];
		$resolver->sortRoutes();

		if($response = $this->container['hooks']->trigger('Asgard.Http.Start', [$request]))
			return $response;
		if($this->start !== null) {
			$container = $this->container;
			if(($response = include $this->start) !== 1)
				return $response;
		}

		$route = $resolver->getRoute($request);
		if($route === null)
			throw new Exceptions\NotFoundException;

		$controllerClass = $route->getController();
		$action = $route->getAction();

		return $this->runController($controllerClass, $action, $request, $route);
	}

	/**
	 * Run the controller and action.
	 * @param  string      $controllerClass
	 * @param  string      $action
	 * @param  Request     $request
	 * @param  null|Route  $route        Route prefix to match.
	 * @return mixed
	 */
	public function runController($controllerClass, $action, Request $request, $route=null) {
		$controller = new $controllerClass();
		$controller->setContainer($this->container);

		$this->addFilters($controller, $action, $request, $route);
		
		if($this->container instanceof \Asgard\Container\Container && $this->container->has('templateEngine'))
			$controller->setTemplateEngine($this->container->make('templateEngine', [$controller]));
		else {
			foreach($this->templatePathSolvers as $cb)
				$controller->addTemplatePathSolver($cb);
		}

		return $controller->run($action, $request);
	}

	/**
	 * Add a template path solver.
	 * @param callable $cb
	 */
	public function addTemplatePathSolver($cb) {
		$this->templatePathSolvers[] = $cb;
	}

	/**
	 * Get a response from an exception.
	 * @param  \Exception $e
	 * @return Response
	 */
	protected function getExceptionResponse(\Exception $e) {
		while(ob_get_length())
			ob_end_clean();

		$this->container['errorHandler']->exceptionHandler($e, false);

		$trace = $this->container['errorHandler']->getBacktraceFromException($e);
		
		if($e instanceof \Asgard\Debug\PSRException)
			$msg = $e->getMessage();
		elseif($e instanceof \ErrorException)
			$msg = 'PHP ('.$this->container['errorHandler']->getPHPError($e->getCode()).'): '.$e->getMessage();
		else
			$msg = get_class($e).': '.$e->getMessage();

		$result = '<b>Message</b><br>'."\n"
			. $msg."<hr>\n"
			. \Asgard\Debug\Debug::getReport($trace);
	
		$response = new \Asgard\Http\Response(500);
		if($this->container['config']['debug'])
			return $response->setHeader('Content-Type', 'text/html')->setContent($result);
		else {
			$msg = isset($this->container['translator']) ? $this->container['translator']->trans('<h1>Error</h1>Oops, something went wrong.'):'<h1>Error</h1>Oops, something went wrong.';
			return $response->setHeader('Content-Type', 'text/html')->setContent($msg);
		}
	}

	/**
	 * Filter all controllers and actions.
	 * @param  string $filter
	 * @param  array $args
	 * @return HttpKernel  $this
	 */
	public function filterAll($filter, $args=[]) {
		$this->filters[] = ['filter'=>$filter, 'args'=>$args];
		return $this;
	}

	/**
	 * Fillter controllers and actions with criteria.
	 * @param  array  $criteria
	 * @param  string $filter
	 * @param  array  $args
	 * @return HttpKernel  $this
	 */
	public function filter($criteria, $filter, $args=[]) {
		$this->filters[] = ['criteria'=>$criteria, 'filter'=>$filter, 'args'=>$args];
		return $this;
	}

	/**
	 * Filter before all controllers and actions.
	 * @param  callable $filter
	 * @return HttpKernel  $this
	 */
	public function filterBeforeAll($filter) {
		$this->beforeFilters[] = ['filter'=>$filter];
		return $this;
	}

	/**
	 * Filter before controllers and actions with criteria.
	 * @param  array    $criteria
	 * @param  callable $filter
	 * @return HttpKernel  $this
	 */
	public function filterBefore($criteria, $filter) {
		$this->beforeFilters[] = ['criteria'=>$criteria, 'filter'=>$filter];
		return $this;
	}

	/**
	 * Filter after all controllers and actions.
	 * @param  callable $filter
	 * @return HttpKernel  $this
	 */
	public function filterAfterAll($filter) {
		$this->afterFilters[] = ['filter'=>$filter];
		return $this;
	}

	/**
	 * Filter after controllers and actions with criteria.
	 * @param  array    $criteria
	 * @param  callable $filter
	 * @return HttpKernel  $this
	 */
	public function filterAfter($criteria, $filter) {
		$this->afterFilters[] = ['criteria'=>$criteria, 'filter'=>$filter];
		return $this;
	}

	/**
	 * Add filters to the controller.
	 * @param Controller   $controller
	 * @param string       $action
	 * @param Request      $request
	 * @param null|Route   $route       Route prefix to match.
	 */
	protected function addFilters(Controller $controller, $action, Request $request, $route=null) {
		$controllerClass = get_class($controller);

		foreach($this->filters as $_filter) {
			$args = $_filter['args'];
			$filter = $_filter['filter'];

			if(isset($criteria)) {
				$criteria = $_filter['criteria'];
				if(isset($criteria['actions'])) {
					if($criteria['actions'] && strpos($controllerClass.':'.$action, $criteria['actions']) !== 0)
						continue;
				}
				if($route !== null && isset($criteria['route'])) {
					foreach($criteria['methods'] as $method) {
						if($criteria['route'] && strpos($route->getRoute(), $criteria['route']) !== 0 || strtoupper($method) !== $request->method())
							continue;
					}
				}
			}

			$reflector = new \ReflectionClass($filter);
			$controller->addFilter($reflector->newInstanceArgs($args));
		}

		foreach($this->beforeFilters as $_filter) {
			$filter = $_filter['filter'];

			if(isset($criteria)) {
				$criteria = $_filter['criteria'];
				if(isset($criteria['actions'])) {
					if($criteria['actions'] && strpos($controllerClass.':'.$action, $criteria['actions']) !== 0)
						continue;
				}
				if($route !== null && isset($criteria['route'])) {
					foreach($criteria['methods'] as $method) {
						if($criteria['route'] && strpos($route->getRoute(), $criteria['route']) !== 0 || strtoupper($method) !== $request->method())
							continue;
					}
				}
			}

			$controller->addBeforeFilter($filter);
		}

		foreach($this->afterFilters as $_filter) {
			$filter = $_filter['filter'];

			if(isset($criteria)) {
				$criteria = $_filter['criteria'];
				if(isset($criteria['actions'])) {
					if($criteria['actions'] && strpos($controllerClass.':'.$action, $criteria['actions']) !== 0)
						continue;
				}
				if($route !== null && isset($criteria['route'])) {
					foreach($criteria['methods'] as $method) {
						if($criteria['route'] && strpos($route->getRoute(), $criteria['route']) !== 0 || strtoupper($method) !== $request->method())
							continue;
					}
				}
			}

			$controller->addAfterFilter($filter);
		}
	}
}
