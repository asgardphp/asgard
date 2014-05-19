<?php
namespace Asgard\Utils;

class JS {
	protected $html;

	public function __construct($html) {
		$this->html = $html;
	}

	public static function placeholder($selector, $placeholder) {
		$this->html->include_js('js/asgard.js');
		$his->html->code('<script>placeholder("'.$selector.'", "'.$placeholder.'")</script>');
	}

	public static function loadJQuery() {
		$this->html->include_js('//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js');
	}
}