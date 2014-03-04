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

		if(\Coxis\Utils\Tools::array_get(\Coxis\Utils\Tools::getallheaders(), 'X-Requested-With') == 'XMLHttpRequest'
			|| \Coxis\Utils\Tools::array_get(\Coxis\Utils\Tools::getallheaders(), 'x-requested-with') == 'XMLHttpRequest')
			return;

		try {
			if($controller->response->getHeader('Content-Type') && $controller->response->getHeader('Content-Type')!='text/html')
				return;
		} catch(\Exception $e) {}

		$layout = $controller->layout;
		$htmllayout = $controller->htmlLayout;

		if(is_array($layout) && sizeof($layout) >= 2 && $result !== null)
			$result = \Coxis\Core\Controller::staticDoRun($layout[0], $layout[1], $result);
		elseif($htmllayout !== true)
			$htmllayout = false;

		if($htmllayout !== false)
			$result = \Coxis\Core\View::renderTemplate('app/general/views/default/html.php', array('content'=>$result));
	}
}