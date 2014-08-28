<?php
namespace Asgard\Templating;

/**
 * Trait for classes using templates.
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
	 * @param  array  $args   arguments
	 * @return string
	 */
	public static function fragment($method, array $args=[]) {
		$class = get_called_class();
		$viewable = new $class;
		return $viewable->run($method, $args);
	}

	/**
	 * Render the method of an instance.
	 * @param  string $method method name
	 * @param  array  $args   arguments
	 * @return string
	 */
	public function run($method, array $args=[]) {
		return $this->runTemplate($method, $args);
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

	/**
	 * Render the template.
	 * @param  string $method method name
	 * @param  array  $args   method arguments
	 * @return string
	 */
	protected function runTemplate($method, array $args=[]) {
		ob_start();
		$result = call_user_func_array([$this, $method], $args);
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
		elseif(isset($this->view) && $this->view) {
			#with given template engine?
			if($this->templateEngine)
				return $this->templateEngine->createTemplate()->setParams((array)$this)->render($this->view);
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
	protected function renderDefaultTemplate($file) {
		if(!file_exists($file))
			$file = $this->solveTemplatePath($orig = $file);
		if(!file_exists($file))
			throw new \Exception('The template file "'.$orig.'" could not be found.');

		extract((array)$this);

		ob_start();
		include($file);
		return ob_get_clean();
	}
}