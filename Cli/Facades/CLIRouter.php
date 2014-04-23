<?php
namespace Asgard\Core\Cli\Facades;

abstract class CLIRouter extends \Asgard\Core\Facade {
	public static function callback() {
		return new \Asgard\Core\Cli\Router;
	}
}