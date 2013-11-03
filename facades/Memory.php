<?php
namespace Coxis\Core\Facades;

abstract class Memory extends \Coxis\Core\Facade {
	public static function callback() {
		return new \Coxis\Core\Memory;
	}
}