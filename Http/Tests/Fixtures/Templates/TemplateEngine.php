<?php
namespace Asgard\Http\Tests\Fixtures\Templates;

class TemplateEngine {
	protected $controller;

	public function templateExists($template) {
		return file_exists(__DIR__.'/'.$template.'.php');
	}

	public function setController($controller) {
		$this->controller = $controller;
		return $this;
	}

	public function getTemplateFile($template) {
		return __DIR__.'/'.$template.'.php';
	}

	public function createTemplate() {
		$template = new Template;
		$template->setEngine($this);
		return $template;
	}
}