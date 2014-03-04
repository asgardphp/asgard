<?php
namespace Asgard\Utils\Facades;

abstract class HTML extends \Asgard\Core\Facade {
	public static function callback() {
		return new \Asgard\Utils\HTML;
	}
}