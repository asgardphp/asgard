<?php
namespace Coxis\Core\Facades;

abstract class Resolver extends \Coxis\Core\Facade {
	public static function callback() {
		return new \Coxis\Core\Resolver;
	}
}