<?php
namespace Asgard\Http;

class PHPTemplate implements TemplateInterface {
	protected $template;
	protected $params = [];
	protected $controller;
	protected $engine;

	public function __construct($template, array $params=[]) {
		$this->setTemplate($template);
		$this->setParams($params);
	}

	public function templateExists() {
		return template_exists($this->template);
	}

	public function setEngine($engine) {
		$this->engine = $engine;
		return $this;
	}

	public function setTemplate($template) {
		$this->template = $template;
		return $this;
	}

	public function getTemplate() {
		return $this->template;
	}

	public function setParams(array $params=[]) {
		$this->params = array_merge($this->params, $params);
		return $this;
	}

	public function getParams() {
		return $this->params;
	}

	public function render($template=null, array $params=[]) {
		if($template === null)
			$template = $this->template;
		$params = array_merge($this->params, $params);

		return static::renderFile($template, $params);
	}

	public static function renderFile($file, array $params=[]) {
		extract($params);

		ob_start();
		include($file);
		return ob_get_clean();
	}
}