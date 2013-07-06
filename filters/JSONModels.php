<?php
namespace Coxis\Core\Filters;
class JSONModels extends Filter {
	public function after($chain, $controller, &$result) {
		if($result!==null) {
			if($result instanceof \Coxis\Core\Model) {
				\Response::setHeader('Content-Type', 'application/json');
				$result = $result->toJSON();
			}
			elseif(is_array($result)) {
				\Response::setHeader('Content-Type', 'application/json');
				$result = Model::arrayToJSON($result);
			}
			else
				return;
		}
	}
}