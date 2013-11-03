<?php
namespace Coxis\Core\Facades;

abstract class Validation extends \Coxis\Core\Facade {
	public static function callback() {
		return new \Coxis\Validation\Validation;
	}
}