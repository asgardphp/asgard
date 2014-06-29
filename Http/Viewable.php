<?php
namespace Asgard\Http;

trait Viewable {
	protected $view;
	protected $templateEngine;
	protected $templatePathSolvers = [];

	public static function fragment($method, array $args=[]) {
		$class = get_called_class();
		$viewable = new $class;
		return $viewable->run($method, $args);
	}

	public function run($method, array $args=[]) {
		return $this->runTemplate($method, $args);
	}

	protected function runTemplate($method, array $args=[]) {
		ob_start();
		$result = call_user_func_array([$this, $method], $args);
		$viewableBuffer = ob_get_clean();

		if($result !== null) {
			if($result instanceof TemplateInterface) {
				if($this->templateEngine)
					$result->setEngine($this->templateEngine);
				return $result->render();
			}
			else
				return $result;
		}
		elseif($viewableBuffer)
			return $viewableBuffer;
		elseif(isset($this->view) && $this->view) {
			if($this->templateEngine)
				return $this->templateEngine->createTemplate()->setParams((array)$this)->render($this->view);
			else
				return $this->renderDefaultTemplate($this->view);
		}
	}

	public function setTemplateEngine($templateEngine) {
		$this->templateEngine = $templateEngine;
		return $this;
	}

	public function getTemplateEngine() {
		return $this->templateEngine;
	}

	public function addTemplatePathSolver($cb) {
		$this->templatePathSolvers[] = $cb;
	}

	protected function solveTemplatePath($file, $template=null) {
		foreach(array_reverse($this->templatePathSolvers) as $s) {
			if(($r = $s($this, $file, $template)) && file_exists($r))
				return $r;
		}
	}

	protected function renderDefaultTemplate($file) {
		if(!file_exists($file))
			$file = $this->solveTemplatePath($orig = $file);
		if(!file_exists($file))
			throw new \Exception('The template file "'.$orig.'" could not be found.');
		$args = (array)$this;

		extract($args);

		ob_start();
		include($file);
		return ob_get_clean();
	}
}