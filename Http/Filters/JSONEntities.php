<?php
namespace Asgard\Http\Filters;

class JSONEntities extends \Asgard\Http\Filter {
	public function after(\Asgard\Hook\HookChain $chain, \Asgard\Http\Controller $controller, &$result) {
		$app = $controller->getApp();

		if($result !== null) {
			if($result instanceof \Asgard\Entity\Entity) {
				$controller->response->setHeader('Content-Type', 'application/json');
				$result = $result->toJSON();
			}
			elseif(is_array($result)) {
				$controller->response->setHeader('Content-Type', 'application/json');
				$result = \Asgard\Entity\Entity::arrayToJSON($result);
			}
		}
	}
}