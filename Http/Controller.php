<?php
namespace Asgard\Http;

/**
 * Controller parent class.
 */
abstract class Controller {
	use \Asgard\Hook\HookableTrait;
	use \Asgard\Templating\ViewableTrait;
	use \Asgard\Container\ContainerAwareTrait;

	/**
	 * Request instance.
	 * @var Request
	 */
	public $request;
	/**
	 * Response instance.
	 * @var Response
	 */
	public $response;
	/**
	 * Called action.
	 * @var string
	 */
	protected $action;
	/**
	 * Before action filters.
	 * @var array
	 */
	protected $beforeFilters = [];
	/**
	 * After action filters.
	 * @var array
	 */
	protected $afterFilters = [];
	/**
	 * Parameters.
	 * @var array
	 */
	protected $parameters = [];
	/**
	 * Flash dependency.
	 * @var Utils\FlashInterface
	 */
	protected $flash;
	/**
	 * Resolver dependency.
	 * @var ResolverInterface
	 */
	protected $resolver;

	/**
	 * Add a filter.
	 * @param Filter $filter
	 */
	public function addFilter(Filter $filter) {
		$this->addBeforeFilter([$filter, 'before']);
		$this->addAfterFilter([$filter, 'after']);
	}

	/**
	 * Add a filter before the action.
	 * @param callable $filter
	 */
	public function addBeforeFilter($filter) {
		$this->beforeFilters[] = $filter;
	}

	/**
	 * Add a filter after the action.
	 * @param callable $filter
	 */
	public function addAfterFilter($filter) {
		$this->afterFilters[] = $filter;
	}

	/**
	 * Run the action.
	 * @param  string  $action
	 * @param  Request $request
	 * @return Response
	 */
	public function run($action, $request=null) {
		$this->action = $action;
		$this->defaultView = $action;

		if($request === null)
			$request = new Request;
		$this->request = $request;
		$this->response = new Response;
		$this->response->setRequest($this->request);

		#before filters
		$result = null;
		foreach($this->beforeFilters as $filter) {
			if(($result = $filter($this, $request)) !== null)
				break;
		}

		if($result === null) {
			if(($result = $this->before($request)) === null)
				$result = $this->doRun($action, [$request]);
		}

		$this->after($request, $result);

		#after filters
		foreach($this->afterFilters as $filter)
			$filter($this, $request, $result);

		if($result instanceof Response)
			return $result;
		elseif(is_string($result))
			return $this->response->setContent($result);

		return $this->response;
	}

	/**
	 * Process the template of an action.
	 * @param  string $method
	 * @param  array  $args
	 * @return string
	 */
	protected function doRun($method, array $args=[]) {
		$method .= 'Action';
		return $this->runTemplate($method, $args);
	}

	/**
	 * To be executed before the action.
	 * @param  Request $request
	 * @return mixed
	 */
	public function before(Request $request) {
	}

	/**
	 * To be executed after the action.
	 * @param  Request $request
	 * @param  mixed            $result
	 */
	public function after(Request $request, &$result) {
	}

	/**
	 * Get the ongoing action.
	 * @return string
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * Return a Flash instance.
	 * @return Utils\Flash
	 */
	public function getFlash() {
		return $this->flash;
	}

	/**
	 * Return to the previous page.
	 * @return Response
	 */
	public function back() {
		return $this->response->redirect($this->request->server['HTTP_REFERER']);
	}

	/**
	 * Throw a "not found" exception.
	 * @param  string $msg
	 * @throws Exception\NotFoundException
	 */
	public function notFound($msg=null) {
		throw new Exception\NotFoundException($msg);
	}

	/**
	 * Return the url for a given action.
	 * @param  mixed $action
	 * @param  array  $params
	 * @return string
	 */
	public function url($action, $params=[]) {
		if(is_array($action))
			return $this->resolver->url($action, $params);
		else
			return $this->resolver->url([get_called_class(), $action], $params);
	}

	/**
	 * Get parameter.
	 * @param  string $name
	 * @return mixed
	 */
	public function get($name) {
		return \Asgard\Common\ArrayUtils::get($this->parameters, $name);
	}

	/**
	 * Set parameter.
	 * @param  string $name
	 * @param  mixed  $value
	 * @return static
	 */
	public function set($name, $value) {
		\Asgard\Common\ArrayUtils::set($this->parameters, $name, $value);
		return $this;
	}

	/**
	 * Check if parameter exists.
	 * @param  string $name
	 * @return boolean
	 */
	public function has($name) {
		return \Asgard\Common\ArrayUtils::has($this->parameters, $name);
	}

	/**
	 * Set the flash dependency.
	 * @param  Utils\FlashInterface $flash
	 * @return static
	 */
	public function setFlash(Utils\FlashInterface $flash=null) {
		$this->flash = $flash;
		return $this;
	}

	/**
	 * Set the resolver dependency.
	 * @param  ResolverInterface $resolver
	 * @return static
	 */
	public function setResolver(ResolverInterface $resolver=null) {
		$this->resolver = $resolver;
		return $this;
	}
}