<?php
namespace Coxis\Core\Facades;

abstract class Hook extends \Coxis\Core\Facade {
	public static function callback() {
		return new \Coxis\Hook\Hook;
	}
}