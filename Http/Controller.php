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
	 * @param  string $action
	 * @param  Request|null $request
	 * @return mixed
	 */
	public function run($action, $request=null) {
		$this->action = $action;
		$this->view = $action;

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
	 * @param  Request $request [description]
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
		return new Utils\Flash($this->request->session);
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
	 * @throws Exceptions\NotFoundException
	 */
	public function notFound($msg=null) {
		throw new Exceptions\NotFoundException($msg);
	}

	/**
	 * Return the url for a given action.
	 * @param  string $action
	 * @param  array  $params
	 * @return string
	 */
	public function url($action, $params=[]) {
		return $this->container['resolver']->url([get_called_class(), $action], $params);
	}
}