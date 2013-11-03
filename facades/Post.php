<?php
namespace Coxis\Core\Facades;

abstract class POST extends \Coxis\Core\Facade {
	public static function callback() {
		return \Request::inst()->post;
	}
}