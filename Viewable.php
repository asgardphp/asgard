<?php
namespace Coxis\Core;

class Viewable extends \Coxis\Hook\Hookable {
	protected $_view;

	public static function widget($action, $args=array()) {
		$viewable = new static;
		return $viewable->doRun($action, $args);
	}

	public static function staticDoRun($class, $method, $params=array()) {
		$viewable = new $class;
		return $viewable->doRun($method, $params);
	}

	public function doRun($method, $params=array()) {
		$this->_view = null;
	
		if(!is_array($params))
			$params = array($params);

		ob_start();
		$result = call_user_func_array(array($this, $method), $params);
		$controllerBuffer =  ob_get_clean();

		if($result !== null)
			return $result;
		if($controllerBuffer)
			return $controllerBuffer;
		elseif($this->_view !== false) {
			$method = preg_replace('/Action$/', '', $method);
			if($this->_view === null)
				if(!$this->setRelativeView($method.'.php'))
					return null;
			return $this->render($this->_view, $this);
		}
		return null;
	}

	public function noView() {
		$this->_view = false;
	}
	
	public function setRelativeView($view) {
		$reflection = new \ReflectionObject($this);
		$dir = dirname($reflection->getFileName());
		$this->setView($dir.'/../views/'.strtolower(preg_replace('/Controller$/i', '', \Coxis\Core\NamespaceUtils::basename(get_class($this)))).'/'.$view);
		return file_exists($dir.'/../views/'.strtolower(preg_replace('/Controller$/i', '', \Coxis\Core\NamespaceUtils::basename(get_class($this)))).'/'.$view);
	}
	
	public function setView($view) {
		$this->_view = $view;
	}
	
	public function render($_view, $_args=array()) {
		foreach($_args as $_key=>$_value)
			$$_key = $_value;

		ob_start();
		include($_view);
		return ob_get_clean();
	}

	public static function staticRender($_viewfile, $_args=array()) {
		foreach($_args as $_key=>$_value)
			$$_key = $_value;

		ob_start();
		include($_viewfile);
		return ob_get_clean();
	}
}