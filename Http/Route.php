<?php
namespace Asgard\Http;

/**
 * Route.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Route {
	/**
	 * Route.
	 * @var string
	 */
	protected $route;
	/**
	 * Controller class.
	 * @var string
	 */
	protected $controller;
	/**
	 * Action name or callback.
	 * @var string|callable
	 */
	protected $action;
	/**
	 * Route options.
	 * @var array
	 */
	protected $options;

	/**
	 * Constructor.
	 * @param string          $route
	 * @param string          $controller
	 * @param string|callable $action
	 * @param array           $options
	 */
	public function __construct($route, $controller, $action, $options=[]) {
		$this->route = $route;
		$this->controller = $controller;
		$this->action = $action;
		$this->options = $options;
	}

	/**
	 * Get an option.
	 * @param  string $name
	 * @return mixed
	 */
	public function get($name) {
		if(!isset($this->options[$name]))
			return;
		return $this->options[$name];
	}

	/**
	 * Set a option.
	 * @param string $name
	 * @param mixed $value
	 */
	public function set($name, $value) {
		$this->options[$name] = $value;
	}

	/**
	 * Get the route.
	 * @return string
	 */
	public function getRoute() {
		return $this->route;
	}

	/**
	 * Set the route.
	 * @param string $route
	 */
	public function setRoute($route) {
		$this->route = $route;
	}

	/**
	 * Get the controller class.
	 * @return string
	 */
	public function getController() {
		return $this->controller;
	}

	/**
	 * Get the action name.
	 * @return string|callable
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * Set the controller class.
	 * @param string $controller
	 */
	public function setController($controller) {
		$this->controller = $controller;
	}

	/**
	 * Set the action name.
	 * @param string|callable $action
	 */
	public function setAction($action) {
		$this->action = $action;
	}
}