<?php
namespace Asgard\Http\Filters;
class PageLayout extends Filter {
	public function before(\Asgard\Hook\HookChain $chain, \Asgard\Http\Controller $controller) {
		if(!isset($controller->layout))
			$controller->layout = null;
		if(!isset($controller->htmlLayout))
			$controller->htmlLayout = null;
	}

	public function after(\Asgard\Hook\HookChain $chain, \Asgard\Http\Controller $controller, &$result) {
		if(!is_string($result))
			return;

		if($controller->request->header->get('x-requested-with') == 'XMLHttpRequest')
			return;

		try {
			if($controller->response->getHeader('Content-Type') && $controller->response->getHeader('Content-Type')!='text/html')
				return;
		} catch(\Exception $e) {}

		$layout = $controller->layout;
		$htmllayout = $controller->htmlLayout;

		if(is_array($layout) && count($layout) >= 2 && $result !== null)
			$result = \Asgard\Http\Controller::staticDoRun($layout[0], $layout[1], $result);
		elseif($htmllayout !== true)
			$htmllayout = false;

		if($htmllayout !== false)
			$result = \Asgard\Http\View::renderTemplate('app/general/views/default/html.php', array('content'=>$result));
	}
}