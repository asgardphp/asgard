<?php
namespace Asgard\Templating;

/**
 * Interface for templates
 */
interface TemplateInterface {
	/**
	 * Return the template engine.
	 * @return TemplateEngineInterface
	 */
	public function getEngine();

	/**
	 * Set the template engine.
	 * @param TemplateEngineInterface $engine
	 */
	public function setEngine(TemplateEngineInterface $engine);

	/**
	 * Set the template file.
	 * @param string $template
	 */
	public function setTemplate($template);

	/**
	 * Return the template file.
	 * @return string
	 */
	public function getTemplate();

	/**
	 * Set the template parameters.
	 * @param array $params
	 */
	public function setParams(array $params=[]);

	/**
	 * Return the template parameters.
	 * @return array
	 */
	public function getParams();

	/**
	 * Render the template.
	 * @param  string $template template name
	 * @param  array  $params   template parameters
	 * @return string
	 */
	public function render($template=null, array $params=[]);

	/**
	 * Render a template file.
	 * @param  string $file   file path
	 * @param  array  $params template parameters
	 * @return string
	 */
	public static function renderFile($file, array $params=[]);
}