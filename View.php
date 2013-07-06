<?php
namespace Coxis\Core;

class View {
	public static function render($_viewfile, $_args=array()) {
		foreach($_args as $_key=>$_value)
			$$_key = $_value;#TODO, watchout keywords

		ob_start();
		\Memory::set('in_view', true);
		include($_viewfile);
		\Memory::set('in_view', false);
		return ob_get_clean();
	}
}