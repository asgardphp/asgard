<?php
namespace Coxis\Core\Filters;
class JSONEntities extends Filter {
	public function after($chain, $controller, &$result) {
		if($result!==null) {
			if($result instanceof \Coxis\Core\Entity) {
				\Coxis\Core\App::get('response')->setHeader('Content-Type', 'application/json');
				$result = $result->toJSON();
			}
			elseif(is_array($result)) {
				\Coxis\Core\App::get('response')->setHeader('Content-Type', 'application/json');
				$result = Entity::arrayToJSON($result);
			}
			else
				return;
		}
	}
}