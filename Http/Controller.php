<?php
namespace Asgard\Http;

abstract class Controller {
	use \Asgard\Hook\HookableTrait;
	use \Asgard\Templating\ViewableTrait;
	use \Asgard\Container\ContainerAwareTrait;
	
	public $request;
	public $response;
	protected $action;
	protected $beforeFilters = [];
	protected $afterFilters = [];

	/* FILTERS */
	public function addFilter($filter) {
		$filter->setController($this);
		$this->addBeforeFilter([$filter, 'before']);
		$this->addAfterFilter([$filter, 'after']);
	}

	public function addBeforeFilter($filter) {
		$this->beforeFilters[] = $filter;
	}

	public function addAfterFilter($filter) {
		$this->afterFilters[] = $filter;
	}

	public function run($action, $request=null) {
		$this->action = $action;
		$this->view = $action;

		if($request === null)
			$request = new Request;
		$this->request = $request;
		$this->response = new Response;

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

	protected function doRun($method, array $args=[]) {
		$method .= 'Action';
		return $this->runTemplate($method, $args);
	}

	public function before(\Asgard\Http\Request $request) {
	}

	public function after(\Asgard\Http\Request $request, &$result) {
	}

	public function getAction() {
		return $this->action;
	}

	/* UTILS */
	public function getFlash() {
		return new \Asgard\Http\Utils\Flash($this->request->session);
	}

	public function back() {
		return $this->response->redirect($this->request->server['HTTP_REFERER']);
	}

	public function notFound($msg=null) {
		throw new Exceptions\NotFoundException($msg);
	}
	
	public function url_for($action, $params=[]) {
		return $this->container['resolver']->url_for([get_called_class(), $action], $params);
	}
}