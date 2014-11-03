<?php
namespace Asgard\Templating;

/**
 * Trait for classes using templates.
 * @author Michel Hognerud <michel@hognerud.com>
 */
trait ViewableTrait {
	/**
	 * Path to view file.
	 * @var string
	 */
	protected $view;
	/**
	 * Template engine.
	 * @var TemplateEngineInterface
	 */
	protected $templateEngine;
	/**
	 * Template path solvers.
	 * @var array<callable>
	 */
	protected $templatePathSolvers = [];

	/**
	 * Render the method of a class.
	 * @param  string $method method name
	 * @param  array  $params   arguments
	 * @return string
	 */
	public function fragment($method, array $params=[]) {
		$this->view = $method;
		return $this->runTemplate($method, $params);
	}

	/**
	 * Render the method of a class statically.
	 * @param  string $method method name
	 * @param  array  $params   arguments
	 * @return string
	 */
	public static function sFragment($method, array $params=[]) {
		$class = get_called_class();
		$v = new $class;
		return $v->fragment($method, $params);
	}

	/**
	 * Set the template engine.
	 * @param TemplateEngineInterface $templateEngine
	 */
	public function setTemplateEngine(TemplateEngineInterface $templateEngine) {
		$this->templateEngine = $templateEngine;
		return $this;
	}

	/**
	 * Return the template engine.
	 * @return TemplateEngineInterface
	 */
	public function getTemplateEngine() {
		return $this->templateEngine;
	}

	/**
	 * Add a callable path solver.
	 * @param callable $cb
	 */
	public function addTemplatePathSolver($cb) {
		$this->templatePathSolvers[] = $cb;
	}

	protected function template($template, array $params=[]) {
		if($this->templateEngine)
			return $this->templateEngine->createTemplate()->setParams((array)$this)->render($template, $params);
		else
			return $this->renderDefaultTemplate($template, $params);
	}

	/**
	 * Render the template.
	 * @param  string $method method name
	 * @param  array  $params   method arguments
	 * @return string
	 */
	protected function runTemplate($method, array $params=[]) {
		ob_start();
		$result         = call_user_func_array([$this, $method], $params);
		$viewableBuffer = ob_get_clean();

		#result returned by method?
		if($result !== null) {
			if($result instanceof TemplateInterface) {
				if(!$result->getEngine() && $this->templateEngine)
					$result->setEngine($this->templateEngine);
				return $result->render();
			}
			else
				return $result;
		}
		#output from method?
		elseif($viewableBuffer)
			return $viewableBuffer;
		#given view?
		elseif($this->view) {
			#with given template engine?
			if($this->templateEngine)
				return $this->templateEngine->createTemplate()->setParams((array)$this)->render($this->view, $params);
			#use the default render technique
			else
				return $this->renderDefaultTemplate($this->view);
		}
	}

	/**
	 * Solve the path to a template.
	 * @param  string $template template name
	 * @return string
	 */
	protected function solveTemplatePath($template) {
		foreach(array_reverse($this->templatePathSolvers) as $s) {
			if(($r = $s($this, $template)) && file_exists($r))
				return $r;
		}
	}

	/**
	 * Render the default template file.
	 * @param  string $file template file
	 * @return string
	 */
	protected function renderDefaultTemplate($file, array $params=null) {
		if(!file_exists($file))
			$file = $this->solveTemplatePath($orig = $file);
		if(!file_exists($file))
			throw new \Exception('The template file "'.$orig.'" could not be found.');

		if($params === null)
			$params = (array)$this;
		extract($params);

		ob_start();
		include($file);
		return ob_get_clean();
	}
}