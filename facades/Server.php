<?php
namespace Coxis\Core\Facades;

abstract class Server extends \Coxis\Core\Facade {
	public static function callback() {
		return \Request::inst()->server;
	}
}