<?php
namespace Asgard\Http;

class ControllerRoute extends Route {
	protected $controller;
	protected $action;

	public function __construct($route, $controller, $action, $options=array()) {
		$this->controller = $controller;
		$this->action = $action;

		parent::__construct($route, array('Asgard\Http\Controller', 'run'), array(), $options);
	}

	public function getController() {
		return $this->controller;
	}

	public function getAction() {
		return $this->action;
	}

	public function setController($controller) {
		$this->controller = $controller;
	}

	public function setAction($action) {
		$this->action = $action;
	}

	public function getArguments() {
		return array($this->controller, $this->action);
	}
}