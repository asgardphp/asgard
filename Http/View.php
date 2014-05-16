<?php
namespace Asgard\Http;

abstract class View {
	protected $template;
	protected $params = array();

	public function reset($template) {
		$this->template = null;
		$this->params = array();
	}

	public function setTemplate($template) {
		$this->template = $template;
		return $this;
	}

	public function getTemplate() {
		return $this->template;
	}

	public function setParams($params) {
		$this->params = $params;
		return $this;
	}

	public function params($params) {
		$this->params = array_merge($this->params, $params);
		return $this;
	}

	public function getParams() {
		return $this->params;
	}

	public function render($params=array()) {
		if(!$params)
			$params = $this->params;
		return static::renderTemplate($this->template, $params);
	}

	public static function renderTemplate($_template, $_params=array()) {
		foreach($_params as $_key=>$_value)
			$$_key = $_value;

		ob_start();
		include($_template);
		return ob_get_clean();
	}
}