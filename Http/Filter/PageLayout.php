<?php
namespace Asgard\Http\Filter;

/**
 * Page layout filter. Adds a layout to the reponse.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class PageLayout extends \Asgard\Http\Filter {
	/**
	 * Layout callback or template.
	 * @var callable|string
	 */
	protected $layout;
	/**
	 * HTML layout callback or template.
	 * @var callable|string
	 */
	protected $htmlLayout;

	/**
	 * Constructor.
	 * @param callable|string $layout
	 * @param callable|string $htmlLayout
	 */
	public function __construct($layout=null, $htmlLayout=null) {
		$this->layout = $layout;
		$this->htmlLayout = $htmlLayout;
	}

	/**
	 * To be executed before the action.
	 * @param  \Asgard\Http\Controller $controller
	 * @param  \Asgard\Http\Request    $request
	 */
	public function before(\Asgard\Http\Controller $controller, \Asgard\Http\Request $request) {
		if(!$controller->has('layout'))
			$controller->set('layout', null);
		if(!$controller->has('htmlLayout'))
			$controller->set('htmlLayout', null);
	}

	/**
	 * To be executed after the action.
	 * @param  \Asgard\Http\Controller $controller
	 * @param  \Asgard\Http\Request    $request
	 * @param  mixed                   $result
	 */
	public function after(\Asgard\Http\Controller $controller, \Asgard\Http\Request $request, &$result) {
		if(!is_string($result) || $controller->request->header['x-requested-with'] == 'XMLHttpRequest')
			return;
		if($controller->response->getHeader('Content-Type') && $controller->response->getHeader('Content-Type') != 'text/html')
			return;

		if($controller->get('layout') !== false) {
			if(is_callable($controller->get('layout')))
				$result = call_user_func_array($controller->get('layout'), [$controller, $result]);
			elseif(is_string($controller->get('layout')))
				$result = \Asgard\Templating\PHPTemplate::renderFile($controller->get('layout'), ['content'=>$result, 'controller'=>$controller]);
			elseif(is_callable($this->layout))
				$result = call_user_func_array($this->layout, [$controller, $result]);
			elseif(is_string($this->layout))
				$result = \Asgard\Templating\PHPTemplate::renderFile($this->layout, ['content'=>$result, 'controller'=>$controller]);
		}

		if($controller->get('htmlLayout') === false)
			return;
		if(is_callable($controller->get('htmlLayout')))
			$result = call_user_func_array($controller->get('htmlLayout'), [$controller, $result]);
		elseif(is_string($controller->get('htmlLayout')))
			$result = \Asgard\Templating\PHPTemplate::renderFile($controller->get('htmlLayout'), ['content'=>$result, 'controller'=>$controller]);
		elseif(is_callable($this->htmlLayout))
			$result = call_user_func_array($this->htmlLayout, [$controller, $result]);
		elseif(is_string($this->htmlLayout))
			$result = \Asgard\Templating\PHPTemplate::renderFile($this->htmlLayout, ['content'=>$result, 'controller'=>$controller]);
	}
}