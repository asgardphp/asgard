<?php
namespace Asgard\Core\Filters;
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

		if(\Asgard\Utils\Tools::array_get(\Asgard\Utils\Tools::getallheaders(), 'X-Requested-With') == 'XMLHttpRequest'
			|| \Asgard\Utils\Tools::array_get(\Asgard\Utils\Tools::getallheaders(), 'x-requested-with') == 'XMLHttpRequest')
			return;

		try {
			if($controller->response->getHeader('Content-Type') && $controller->response->getHeader('Content-Type')!='text/html')
				return;
		} catch(\Exception $e) {}

		$layout = $controller->layout;
		$htmllayout = $controller->htmlLayout;

		if(is_array($layout) && sizeof($layout) >= 2 && $result !== null)
			$result = \Asgard\Core\Controller::staticDoRun($layout[0], $layout[1], $result);
		elseif($htmllayout !== true)
			$htmllayout = false;

		if($htmllayout !== false)
			$result = \Asgard\Core\View::renderTemplate('app/general/views/default/html.php', array('content'=>$result));
	}
}