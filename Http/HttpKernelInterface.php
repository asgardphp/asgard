<?php
namespace Asgard\Http;

/**
 * HTTP Kernel.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface HttpKernelInterface {
	/**
	 * Set debug flag.
	 * @param boolean $debug
	 * @return HttpKernelInterface $this
	 */
	public function setDebug($debug);

	/**
	 * Set translator dependency.
	 * @param \Symfony\Component\Translation\TranslatorInterface $translator
	 * @return HttpKernelInterface $this
	 */
	public function setTranslator(\Symfony\Component\Translation\TranslatorInterface $translator);

	/**
	 * Set Resolver dependency.
	 * @param ResolverInterface $resolver
	 * @return HttpKernelInterface $this
	 */
	public function setResolver(ResolverInterface $resolver);

	/**
	 * Set Error handler dependency.
	 * @param \Asgard\Debug\ErrorHandler $errorHandler
	 * @return HttpKernelInterface $this
	 */
	public function setErrorHandler(\Asgard\Debug\ErrorHandler $errorHandler);

	/**
	 * Set Hooks manager dependency.
	 * @param \Asgard\Hook\HookManagerInterface $HookManager
	 * @return HttpKernelInterface $this
	 */
	public function setHookManager(\Asgard\Hook\HookManagerInterface $HookManager);

	/**
	 * Set template engine factory.
	 * @param \Asgard\Templating\TemplateEngineFactoryInterface $templateEngineFactory
	 * @return HttpKernelInterface $this
	 */
	public function setTemplateEngineFactory(\Asgard\Templating\TemplateEngineFactoryInterface $templateEngineFactory);

	/**
	 * Return the HookManager.
	 * @return \Asgard\Hook\HookManagerInterface
	 */
	public function getHookManager();

	/**
	 * Return the resolver.
	 * @return ResolverInterface
	 */
	public function getResolver();

	/**
	 * Return the debug flag.
	 * @return boolean
	 */
	public function getDebug();

	/**
	 * Return the translator
	 * @return \Symfony\Component\Translation\TranslatorInterface
	 */
	public function getTranslator();

	/**
	 * Return the error handler.
	 * @return \Asgard\Debug\ErrorHandler
	 */
	public function getErrorHandler();

	/**
	 * Return the HookManager
	 * @return \Asgard\Templating\TemplateEngineFactoryInterface
	 */
	public function getTemplateEngineFactory();

	/**
	 * Set the start file.
	 * @param  string $start
	 */
	public function start($start);

	/**
	 * Set the end file.
	 * @param  string $end
	 */
	public function end($end);

	/**
	 * Run the kernel and return a response.
	 * @return Response
	 */
	public function run();

	/**
	 * Set the HTTP request.
	 * @param  Request $request
	 * @return HttpKernelInterface $this
	 */
	public function addRequest(Request $request);

	/**
	 * Process the request.
	 * @param  Request $request
	 * @param  boolean $catch   true to catch exceptions.
	 * @return Response
	 */
	public function process(Request $request, $catch=true);

	/**
	 * Get the last given request.
	 * @return Request
	 */
	public function getRequest();

	/**
	 * Run the controller and action.
	 * @param  string          $controllerClass
	 * @param  string|callable $action
	 * @param  Request         $request
	 * @param  Route           $route     Route prefix to match.
	 * @return mixed
	 */
	public function runController($controllerClass, $action, Request $request, Route $route=null);

	/**
	 * Add a template path solver.
	 * @param callable $cb
	 */
	public function addTemplatePathSolver($cb);

	/**
	 * Filter all controllers and actions.
	 * @param  string $filter
	 * @param  array $args
	 * @return HttpKernelInterface  $this
	 */
	public function filterAll($filter, $args=[]);

	/**
	 * Fillter controllers and actions with criteria.
	 * @param  array  $criteria
	 * @param  string $filter
	 * @param  array  $args
	 * @return HttpKernelInterface  $this
	 */
	public function filter($criteria, $filter, $args=[]);

	/**
	 * Filter before all controllers and actions.
	 * @param  callable $filter
	 * @return HttpKernelInterface  $this
	 */
	public function filterBeforeAll($filter);

	/**
	 * Filter before controllers and actions with criteria.
	 * @param  array    $criteria
	 * @param  callable $filter
	 * @return HttpKernelInterface  $this
	 */
	public function filterBefore($criteria, $filter);

	/**
	 * Filter after all controllers and actions.
	 * @param  callable $filter
	 * @return HttpKernelInterface  $this
	 */
	public function filterAfterAll($filter);

	/**
	 * Filter after controllers and actions with criteria.
	 * @param  array    $criteria
	 * @param  callable $filter
	 * @return HttpKernelInterface  $this
	 */
	public function filterAfter($criteria, $filter);
}