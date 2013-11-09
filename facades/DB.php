<?php
namespace Coxis\DB\Facades;

abstract class DB extends \Coxis\Core\Facade {
	public static function callback() {
		return new \Coxis\DB\DB(\Config::get('database'));
	}
}