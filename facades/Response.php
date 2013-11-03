<?php
namespace Coxis\Core\Facades;

abstract class Response extends \Coxis\Core\Facade {
	public static function callback() {
		return new \Coxis\Core\Response;
	}
}