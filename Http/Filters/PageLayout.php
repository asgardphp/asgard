<?php
namespace Asgard\Http\Filters;

class PageLayout extends \Asgard\Http\Filter {
	protected $layout;
	protected $htmlLayout;

	public function __construct($layout=null, $htmlLayout=null) {
		$this->layout = $layout;
		$this->htmlLayout = $htmlLayout;
	}

	public function before(\Asgard\Hook\HookChain $chain, \Asgard\Http\Controller $controller) {
		if(!isset($controller->layout))
			$controller->layout = null;
		if(!isset($controller->htmlLayout))
			$controller->htmlLayout = null;
	}

	public function after(\Asgard\Hook\HookChain $chain, \Asgard\Http\Controller $controller, &$result) {
		if(!is_string($result) || $controller->request->header['x-requested-with'] == 'XMLHttpRequest')
			return;
		if($controller->response->getHeader('Content-Type') && $controller->response->getHeader('Content-Type') != 'text/html')
			return;

		if($controller->layout !== false) {
			if(is_callable($controller->layout))
				$result = call_user_func_array($controller->layout, [$result]);
			elseif(is_string($controller->layout))
				$result = \Asgard\Http\View::renderTemplate($controller->layout, ['content'=>$result, 'controller'=>$controller]);
			elseif(is_callable($this->layout))
				$result = call_user_func_array($this->layout, [$result]);
			elseif(is_string($this->layout))
				$result = \Asgard\Http\View::renderTemplate($this->layout, ['content'=>$result, 'controller'=>$controller]);
		}

		if($controller->htmlLayout === false)
			return;
		if(is_callable($controller->htmlLayout))
			$result = call_user_func_array($controller->htmlLayout, [$result]);
		elseif(is_string($controller->htmlLayout))
			$result = \Asgard\Http\View::renderTemplate($controller->htmlLayout, ['content'=>$result, 'controller'=>$controller]);
		elseif(is_callable($this->htmlLayout))
			$result = call_user_func_array($this->htmlLayout, [$result]);
		elseif(is_string($this->htmlLayout))
			$result = \Asgard\Http\View::renderTemplate($this->htmlLayout, ['content'=>$result, 'controller'=>$controller]);
	}
}