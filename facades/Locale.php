<?php
namespace Coxis\Core\Facades;

abstract class Locale extends \Coxis\Core\Facade {
	public static function callback() {
		return new \Coxis\Utils\Locale;
	}
}