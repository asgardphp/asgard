<?php
namespace Coxis\Core\Facades;

abstract class ModelsManager extends \Coxis\Core\Facade {
	public static function callback() {
		return new \Coxis\Core\ModelsManager;
	}
}