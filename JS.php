<?php
namespace Coxis\Utils;

class JS {
	public static function placeholder($selector, $placeholder) {
		\HTML::include_js('js/coxis.js');
		\HTML::code('<script>placeholder("'.$selector.'", "'.$placeholder.'")</script>');
	}
}