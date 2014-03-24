<?php
namespace Asgard\Core\Filters;
class JSONEntities extends Filter {
	public function after($chain, $controller, &$result) {
		if($result!==null) {
			if($result instanceof \Asgard\Core\Entity) {
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