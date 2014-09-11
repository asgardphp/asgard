<?php
namespace Asgard\Http;

/**
 * Controller for actions without a controller.
 */
class LambdaController extends Controller {
	/**
	 * Run the action method.
	 * @param  string $method
	 * @param  array  $args
	 * @return mixed
	 */
	protected function doRun($method, array $args=[]) {
		ob_start();
		$result = call_user_func_array($method, array_merge([$this], $args));
		$controllerBuffer =  ob_get_clean();

		if($result !== null)
			return $result;
		if($controllerBuffer)
			return $controllerBuffer;
		return null;
	}
}