<?php
namespace Asgard\Http;

class LambdaController extends Controller {
	protected function doRun($method, array $params=[]) {
		$this->_view = null;

		ob_start();
		$result = call_user_func_array($method, array_merge([$this], $params));
		$controllerBuffer =  ob_get_clean();

		if($result !== null)
			return $result;
		if($controllerBuffer)
			return $controllerBuffer;
		return null;
	}
}