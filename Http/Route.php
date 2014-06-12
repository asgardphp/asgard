<?php
namespace Asgard\Http;

class Route {
	protected $route;
	protected $controller;
	protected $callback;
	protected $arguments;
	protected $options;

	public function __construct($route, $callback, $arguments=[], $options=[]) {
		$this->route = $route;
		$this->callback = $callback;
		$this->arguments = $arguments;
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

	public function getCallback() {
		return $this->callback;
	}

	public function getArguments() {
		return $this->arguments;
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