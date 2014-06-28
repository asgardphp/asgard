<?php
namespace Asgard\Http;

abstract class Viewable {
	protected $_view;

	public static function fragment($class, $method, array $params=[]) {
		$viewable = new $class;
		return $viewable->doRun($method, $params);
	}

	protected function doRun($method, array $params=[]) {
		$this->_view = null;

		ob_start();
		$result = call_user_func_array([$this, $method], $params);
		$viewableBuffer =  ob_get_clean();

		if($result !== null)
			return $result;
		if($viewableBuffer)
			return $viewableBuffer;
		elseif($this->_view !== false) {
			if($this->_view instanceof View)
				return $this->_view->render();
			else {
				if($this->_view === null)
					return null;
				return $this->renderView($this->_view, (array)$this);
			}
		}
		return null;
	}
	
	protected function renderView($_view, array $_args=[]) {
		foreach($_args as $_key=>$_value)
			$$_key = $_value;

		ob_start();
		include($_view);
		return ob_get_clean();
	}

	public function noView() {
		$this->_view = false;
	}
	
	public function setView($view) {
		$this->_view = $view;
	}
}