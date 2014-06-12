<?php
namespace Asgard\Http;

class View {
	protected $template;
	protected $params = [];

	public function __construct($template, array $params=[]) {
		$this->template = $template;
		$this->params = $params;
	}

	public function reset() {
		$this->template = null;
		$this->params = [];
	}

	public function template($template) {
		$this->template = $template;
		return $this;
	}

	public function getTemplate() {
		return $this->template;
	}

	public function params(array $params=[]) {
		$this->params = array_merge($this->params, $params);
		return $this;
	}

	public function getParams() {
		return $this->params;
	}

	public function render($_template=null, array $_params=[]) {
		if($_template === null)
			$_template = $this->template;
		$_params = array_merge($this->params, $_params);

		return static::renderTemplate($_template, $_params);
	}

	public static function renderTemplate($_template, array $_params=array()) {
		foreach($_params as $_key=>$_value)
			$$_key = $_value;

		ob_start();
		include($_template);
		return ob_get_clean();
	}
}