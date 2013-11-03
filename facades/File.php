<?php
namespace Coxis\Core\Facades;

abstract class File extends \Coxis\Core\Facade {
	public static function callback() {
		return \Request::inst()->file;
	}
}