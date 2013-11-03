<?php
namespace Coxis\Core\Facades;

abstract class GET extends \Coxis\Core\Facade {
	public static function callback() {
		return \Request::inst()->get;
	}
}