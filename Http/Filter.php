<?php
namespace Asgard\Http;

/**
 * Action filter.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Filter {
	/**
	 * Filter parameters.
	 * @var array
	 */
	protected $params;

	/**
	 * Constructor.
	 * @param array $params
	 */
	public function __construct(array $params=[]) {
		$this->params = $params;
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