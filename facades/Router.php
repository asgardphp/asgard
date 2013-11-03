<?php
namespace Coxis\Core\Facades;

abstract class Router extends \Coxis\Core\Facade {
	public static function callback() {
		return new \Coxis\Core\Router;
	}
}