<?php
namespace Asgard\Http;

/**
 * HTTP Kernel.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class HttpKernel implements HttpKernelInterface {
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
	 * Hooks manager dependency.
	 * @var \Asgard\Hook\HooksManagerInterface
	 */
	protected $hooksManager;
	/**
	 * Error handler dependency.
	 * @var \Asgard\Debug\ErrorHandler
	 */
	protected $errorHandler;
	/**
	 * Translator dependency.
	 * @var \Symfony\Component\Translation\TranslatorInterface
	 */
	protected $translator;
	/**
	 * Debug flag.
	 * @var boolean
	 */
	protected $debug = false;
	/**
	 * Template engine factory.
	 * @var \Asgard\Container\Factory
	 */
	protected $templateEngineFactory;
	/**
	 * Resolver dependency.
	 * @var ResolverInterface
	 */
	protected $resolver;

	/**
	 * Constructor.
	 * @param \Asgard\Container\ContainerInterface $container
	 */
	public function __construct(\Asgard\Container\ContainerInterface $container=null) {
		$this->container = $container;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDebug($debug) {
		$this->debug = $debug;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setTranslator(\Symfony\Component\Translation\TranslatorInterface $translator) {
		$this->translator = $translator;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setResolver(ResolverInterface $resolver) {
		$this->resolver = $resolver;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setErrorHandler(\Asgard\Debug\ErrorHandler $errorHandler) {
		$this->errorHandler = $errorHandler;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setHooksManager(\Asgard\Hook\HooksManagerInterface $hooksManager) {
		$this->hooksManager = $hooksManager;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setTemplateEngineFactory(\Asgard\Container\Factory $templateEngineFactory) {
		$this->templateEngineFactory = $templateEngineFactory;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHooksManager() {
		return $this->hooksManager;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getResolver() {
		if(!$this->resolver)
			$this->resolver = new Resolver();
		return $this->resolver;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDebug() {
		return $this->debug;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTranslator() {
		return $this->translator;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorHandler() {
		return $this->errorHandler;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplateEngineFactory() {
		return $this->templateEngineFactory;
	}

	/**
	 * {@inheritDoc}
	 */
	public function start($start) {
		$this->start = $start;
	}

	/**
	 * {@inheritDoc}
	 */
	public function end($end) {
		$this->end = $end;
	}

	/**
	 * {@inheritDoc}
	 */
	public function run() {
		$request = Request::singleton();
		$request->isInitial = true;

		$response = $this->process($request);

		$this->hooksManager->trigger('Asgard.Http.Output', [$response, $request]);
		return $response;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addRequest(Request $request) {
		$this->requests[] = $request;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function process(Request $request, $catch=true) {
		$this->addRequest($request);

		if(!$catch) {
			$response = $this->processRaw($request);
			if(!$response instanceof Response)
				$response = (new Response())->setRequest($request)->setContent($response);
		}
		else {
			try {
				$response = $this->processRaw($request);
				if(!$response instanceof Response)
					$response = (new Response())->setRequest($request)->setContent($response);
			} catch(\Exception $e) {
				if($e instanceof ControllerException) {
					$response = $e->getResponse();
					$severity = $e->getSeverity();
					$trace = $this->errorHandler->getBacktraceFromException($e);
					$this->errorHandler->log($severity, $e->getMessage(), $e->getFile(), $e->getLine(), $trace);
				}
				else {
					$response = null;
					$this->errorHandler->logException($e);
				}

				$this->hooksManager->trigger('Asgard.Http.Exception.'.get_class($e), [$e, &$response, $request]);
				if($response === null)
					$response = $this->getExceptionResponse($e);
			}
		}

		try {
			if($this->end !== null)
				include $this->end;
			$this->hooksManager->trigger('Asgard.Http.End', [$response]);
		} catch(\Exception $e) {
			$this->errorHandler->logException($e);
		}

		array_pop($this->requests);

		return $response;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRequest() {
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
		$resolver = $this->getResolver();
		$resolver->sortRoutes();

		if($response = $this->hooksManager->trigger('Asgard.Http.Start', [$request]))
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
	 * {@inheritDoc}
	 */
	public function runController($controllerClass, $action, Request $request, Route $route=null) {
		$controller = new $controllerClass();
		$controller->setContainer($this->container);

		$this->addFilters($controller, $action, $request, $route);

		if($this->templateEngineFactory)
			$controller->setTemplateEngine($this->templateEngineFactory->create([$controller]));
		else {
			foreach($this->templatePathSolvers as $cb)
				$controller->addTemplatePathSolver($cb);
		}

		return $controller->run($action, $request);
	}

	/**
	 * {@inheritDoc}
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

		$this->errorHandler->exceptionHandler($e, false);

		$trace = $this->errorHandler->getBacktraceFromException($e);

		if($e instanceof \Asgard\Debug\PSRException)
			$msg = $e->getMessage();
		elseif($e instanceof \ErrorException)
			$msg = 'PHP ('.$this->errorHandler->getPHPError($e->getCode()).'): '.$e->getMessage();
		else
			$msg = get_class($e).': '.$e->getMessage();

		$result = '<b>Message</b><br>'."\n"
			. $msg."<hr>\n"
			. \Asgard\Debug\Debug::getReport($trace);

		$response = new \Asgard\Http\Response(500);
		if($this->debug)
			return $response->setHeader('Content-Type', 'text/html')->setContent($result);
		else {
			$msg = isset($this->translator) ? $this->translator->trans('<h1>Error</h1>Oops, something went wrong.'):'<h1>Error</h1>Oops, something went wrong.';
			return $response->setHeader('Content-Type', 'text/html')->setContent($msg);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function filterAll($filter, $args=[]) {
		$this->filters[] = ['filter'=>$filter, 'args'=>$args];
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function filter($criteria, $filter, $args=[]) {
		$this->filters[] = ['criteria'=>$criteria, 'filter'=>$filter, 'args'=>$args];
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function filterBeforeAll($filter) {
		$this->beforeFilters[] = ['filter'=>$filter];
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function filterBefore($criteria, $filter) {
		$this->beforeFilters[] = ['criteria'=>$criteria, 'filter'=>$filter];
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function filterAfterAll($filter) {
		$this->afterFilters[] = ['filter'=>$filter];
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function filterAfter($criteria, $filter) {
		$this->afterFilters[] = ['criteria'=>$criteria, 'filter'=>$filter];
		return $this;
	}

	/**
	 * Add filters to the controller.
	 * @param Controller      $controller
	 * @param string|callable $action
	 * @param Request         $request
	 * @param Route           $route      Route prefix to match.
	 */
	protected function addFilters(Controller $controller, $action, Request $request, Route $route=null) {
		$controllerClass = get_class($controller);

		foreach($this->filters as $_filter) {
			$args = $_filter['args'];
			$filter = $_filter['filter'];

			if(isset($criteria)) {
				$criteria = $_filter['criteria'];
				if(isset($criteria['actions'])) {
					if(!is_string($action))
						continue;
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
					if(!is_string($action))
						continue;
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
