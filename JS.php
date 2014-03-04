<?php
namespace Coxis\Utils;

class JS {
	public static function placeholder($selector, $placeholder) {
		\Coxis\Core\App::get('html')->include_js('js/coxis.js');
		\Coxis\Core\App::get('html')->code('<script>placeholder("'.$selector.'", "'.$placeholder.'")</script>');
	}

	public static function loadJQuery() {
		\Coxis\Core\App::get('html')->include_js('//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js');
	}
}