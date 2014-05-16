<?php
namespace Asgard\Http\Filters;
class JSONEntities extends Filter {
	public function after(\Asgard\Hook\HookChain $chain, \Asgard\Http\Controller $controller, &$result) {
		if($result!==null) {
			if($result instanceof \Asgard\Entity\Entity) {
				\Asgard\Core\App::get('response')->setHeader('Content-Type', 'application/json');
				$result = $result->toJSON();
			}
			elseif(is_array($result)) {
				\Asgard\Core\App::get('response')->setHeader('Content-Type', 'application/json');
				$result = Entity::arrayToJSON($result);
			}
			else
				return;
		}
	}
}