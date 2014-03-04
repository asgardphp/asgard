<?php
namespace Asgard\Utils;

class JS {
	public static function placeholder($selector, $placeholder) {
		\Asgard\Core\App::get('html')->include_js('js/asgard.js');
		\Asgard\Core\App::get('html')->code('<script>placeholder("'.$selector.'", "'.$placeholder.'")</script>');
	}

	public static function loadJQuery() {
		\Asgard\Core\App::get('html')->include_js('//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js');
	}
}