<?php
namespace Asgard\Http;

class Route {
	protected $route;
	protected $controller;
	protected $action;
	protected $options;

	public function __construct($route, $controller, $action, $options=[]) {
		$this->route = $route;
		$this->controller = $controller;
		$this->action = $action;
		$this->options = $options;
	}

	public function get($name) {
		if(!isset($this->options[$name]))
			return;
		return $this->options[$name];
	}

	public function set($name, $value) {
		$this->options[$name] = $value;
	}

	public function getRoute() {
		return $this->route;
	}

	public function getController() {
		return $this->controller;
	}

	public function getAction() {
		return $this->action;
	}

	public function setRoute($route) {
		$this->route = $route;
	}

	public function setCallback($callback) {
		$this->callback = $callback;
	}

	public function setArguments($arguments) {
		$this->arguments = $arguments;
	}
}