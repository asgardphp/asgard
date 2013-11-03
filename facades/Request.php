<?php
namespace Coxis\Core\Facades;

class Request extends \Coxis\Core\Facade {
	public static function callback() {
		return \Coxis\Core\Request::createFromGlobals();
	}
}