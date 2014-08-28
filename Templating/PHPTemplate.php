<?php
namespace Asgard\Templating;

/**
 * Template using PHP for formatting
 */
class PHPTemplate implements TemplateInterface {
	/**
	 * Path to template file
	 * @var string
	 */
	protected $template;
	/**
	 * Array of parameters for the template
	 * @var array
	 */
	protected $params = [];

	/**
	 * Constructor.
	 * @param string $template path to template file
	 * @param array $params   parameters for template
	 */
	public function __construct($template, array $params=[]) {
		$this->setTemplate($template);
		$this->setParams($params);
	}

	/**
	 * Required by TemplateInterface but unecessary for PHPTemplate.
	 */
	public function getEngine() {}

	/**
	 * Required by TemplateInterface but unecessary for PHPTemplate.
	 * @param TemplateEngineInterface $engine
	 */
	public function setEngine(TemplateEngineInterface $engine) {}

	/**
	 * Set template file
	 * @param string $template
	 */
	public function setTemplate($template) {
		$this->template = $template;
		return $this;
	}

	/**
	 * Return template file
	 * @return string
	 */
	public function getTemplate() {
		return $this->template;
	}

	/**
	 * Set template parameters
	 * @param array $params
	 */
	public function setParams(array $params=[]) {
		$this->params = array_merge($this->params, $params);
		return $this;
	}

	/**
	 * Return template parameters
	 * @return array
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * Render the template
	 * @param  string $template path to template file
	 * @param  array  $params   template parameters
	 * @return string           result
	 */
	public function render($template=null, array $params=[]) {
		if($template === null)
			$template = $this->template;
		$params = array_merge($this->params, $params);

		return static::renderFile($template, $params);
	}

	/**
	 * Render a template
	 * @param  string $file   path to template file
	 * @param  array  $params template parameters
	 * @return string         result
	 */
	public static function renderFile($file, array $params=[]) {
		extract($params);

		ob_start();
		include($file);
		return ob_get_clean();
	}
}