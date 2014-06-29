<?php
namespace Asgard\Http;

interface TemplateInterface {
	public function setEngine($engine);

	public function setTemplate($template);

	public function getTemplate();

	public function setParams(array $params=[]);

	public function getParams();

	public function render($template=null, array $params=[]);

	public static function renderFile($file, array $params=[]);
}