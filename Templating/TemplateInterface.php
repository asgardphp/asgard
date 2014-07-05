<?php
namespace Asgard\Templating;

interface TemplateInterface {
	public function getEngine();

	public function setEngine(TemplateEngineInterface $engine);

	public function setTemplate($template);

	public function getTemplate();

	public function setParams(array $params=[]);

	public function getParams();

	public function render($template=null, array $params=[]);

	public static function renderFile($file, array $params=[]);
}