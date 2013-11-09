<?php
namespace Coxis\Utils\Facades;

abstract class HTML extends \Coxis\Core\Facade {
	public static function callback() {
		return new \Coxis\Utils\HTML;
	}
}