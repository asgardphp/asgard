<?php
namespace Asgard\Http\Tests\Fixtures\Templates;

class TemplateEngineFactory implements \Asgard\Templating\TemplateEngineFactoryInterface {
	public function create(\Asgard\Http\Controller $controller) {
		$engine = new TemplateEngine;
		$engine->setController($controller);
		return $engine;
	}
}