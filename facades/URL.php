<?php
namespace Coxis\Core\Facades;

abstract class URL extends \Coxis\Core\Facade {
	public static function callback() {
		return \Request::inst()->url;
	}
}