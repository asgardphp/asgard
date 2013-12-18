<?php
namespace Coxis\Core\Facades;

abstract class EntitiesManager extends \Coxis\Core\Facade {
	public static function callback() {
		return new \Coxis\Core\EntitiesManager;
	}
}