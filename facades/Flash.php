<?php
namespace Coxis\Core\Facades;

abstract class Flash extends \Coxis\Core\Facade {
	public static function callback() {
		return new \Coxis\Utils\Flash;
	}
}