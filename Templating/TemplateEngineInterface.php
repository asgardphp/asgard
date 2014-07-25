<?php
namespace Asgard\Templating;

interface TemplateEngineInterface {
	public function templateExists($template);
	public function getTemplateFile($template);
	public function createTemplate();
}