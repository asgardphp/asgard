<?php
namespace Asgard\Core\Filters;
class Filter {
	protected $controller;
	protected $params;

	function __construct($params=array(), $controller=null) {
		$this->controller = $controller;
		$this->params = $params;
	}

	public function setController($controller) {
		$this->controller = $controller;
	}

	public function getBeforePriority() {
		return isset($this->params['beforePriority']) ? $this->params['beforePriority']:0;
	}
	public function getAfterPriority() {
		return isset($this->params['afterPriority']) ? $this->params['afterPriority']:0;
	}
}