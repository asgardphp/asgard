<?php
namespace Asgard\Http;

/**
 * Action filter.
 */
class Filter {
	/**
	 * Controller class.
	 * @var string
	 */
	protected $controller;
	/**
	 * Filter parameters.
	 * @var array
	 */
	protected $params;

	/**
	 * Constructor.
	 * @param array $params
	 * @param string $controller
	 */
	public function __construct(array $params=[], $controller=null) {
		$this->controller = $controller;
		$this->params = $params;
	}

	/**
	 * Set the controller class.
	 * @param string $controller
	 */
	public function setController($controller) {
		$this->controller = $controller;
	}

	/**
	 * Method to be executed before the action.
	 * @param  Controller    $controller
	 * @param  Request       $request
	 * @return mixed
	 */
	public function before(Controller $controller, Request $request) {
	}

	/**
	 * Method to be executed after the action.
	 * @param  Controller    $controller
	 * @param  Request       $request
	 * @param  mixed         $result
	 */
	public function after(Controller $controller, Request $request, &$result) {
	}
}