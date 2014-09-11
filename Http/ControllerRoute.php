<?php
namespace Asgard\Http;

/**
 * Controller route.
 */
class ControllerRoute extends Route {
	/**
	 * Controller class.
	 * @var string
	 */
	protected $controller;
	/**
	 * Action name.
	 * @var string
	 */
	protected $action;

	/**
	 * Constructor.
	 * @param string $route
	 * @param string $controller
	 * @param string $action
	 * @param array  $options
	 */
	public function __construct($route, $controller, $action, array $options=[]) {
		$this->controller = $controller;
		$this->action = $action;

		parent::__construct($route, ['Asgard\Http\Controller', 'staticRun'], [], $options);
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
	 * @return string
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
	 * @param string $action
	 */
	public function setAction($action) {
		$this->action = $action;
	}
}