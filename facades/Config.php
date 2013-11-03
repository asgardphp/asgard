<?php
namespace Coxis\Core\Facades;

abstract class Config extends \Coxis\Core\Facade {
	public static function callback() {
		return new \Coxis\Core\Config;
	}
}