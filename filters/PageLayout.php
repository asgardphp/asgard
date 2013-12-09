<?php
namespace Coxis\Core\Filters;
class PageLayout extends Filter {
	public function before($chain, $controller) {
		if(!isset($controller->layout))
			$controller->layout = null;
		if(!isset($controller->htmlLayout))
			$controller->htmlLayout = null;
	}

	public function after($chain, $controller, &$result) {
		if(!is_string($result))
			return;

		if(function_exists('getallheaders')) {
			if(\Coxis\Utils\Tools::get(\getallheaders(), 'X-Requested-With') == 'XMLHttpRequest'
				|| \Coxis\Utils\Tools::get(\getallheaders(), 'x-requested-with') == 'XMLHttpRequest')
				return;
		}

		try {
			if(\Response::getHeader('Content-Type') && \Response::getHeader('Content-Type')!='text/html')
				return;
		} catch(\Exception $e) {}

		$layout = $controller->layout;
		$htmllayout = $controller->htmlLayout;

		if(is_array($layout) && sizeof($layout) >= 2 && $result !== null)
			$result = Viewable::staticDoRun($layout[0], $layout[1], $result);
		elseif($htmllayout !== true)
			$htmllayout = false;

		if($htmllayout !== false)
			$result = \Coxis\Core\View::render('app/general/views/default/html.php', array('content'=>$result));
	}
}