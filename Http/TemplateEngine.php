<?php
namespace Asgard\Http;

interface TemplateEngine {
	public function templateExists($template);
	public function getTemplateFile($template);
	public function setController($controller);
	public function createView();
}