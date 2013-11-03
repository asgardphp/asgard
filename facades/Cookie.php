<?php
namespace Coxis\Core\Facades;

abstract class Cookie extends \Coxis\Core\Facade {
	public static function callback() {
		return \Request::inst()->cookie;
	}
}