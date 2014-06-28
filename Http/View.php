<?php
namespace Asgard\Http;

class View {
	protected $file;
	protected $params = [];
	protected $controller;

	public function __construct($file, array $params=[]) {
		$this->setFile($file);
		$this->setParams($params);
	}

	public function fileExists() {
		return file_exists($this->file);
	}

	public function setController($controller) {
		$this->controller = $controller;
		return $this;
	}

	public function setFile($file) {
		$this->file = $file;
		return $this;
	}

	public function getFile() {
		return $this->file;
	}

	public function setParams(array $params=[]) {
		$this->params = array_merge($this->params, $params);
		return $this;
	}

	public function getParams() {
		return $this->params;
	}

	public function render($file=null, array $params=[]) {
		if($file === null)
			$file = $this->file;
		$params = array_merge($this->params, $params);

		return static::renderFile($file, $params);
	}

	public static function renderFile($file, array $params=[]) {
		extract($params);

		ob_start();
		include($file);
		return ob_get_clean();
	}
}