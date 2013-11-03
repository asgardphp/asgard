<?php
namespace Coxis\Core\Facades;

abstract class Session extends \Coxis\Core\Facade {
	public static function callback() {
		return \Request::inst()->session;
	}
}