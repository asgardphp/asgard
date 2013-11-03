<?php
namespace Coxis\Core\Facades;

class Importer extends \Coxis\Core\Facade {
	public static function callback() {
		return new \Coxis\Core\Importer;
	}
}