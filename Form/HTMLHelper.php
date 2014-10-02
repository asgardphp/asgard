<?php
namespace Asgard\Form;

/**
 * Helper to create HTML tags.
 */
class HTMLHelper {
	/**
	 * Create HTML tag.
	 * @param  string $tag
	 * @param  array  $attrs
	 * @param  string $inner   inner html
	 * @return string
	 */
	public static function tag($tag, array $attrs, $inner=null) {
		$str = '';

		foreach($attrs as $k=>$v)
			$str .= $k.'="'.$v.'" ';

		$str = '<'.$tag.' '.trim($str).'>';

		if($inner === null)
			return $str;

		$str .= $inner;

		return $str.'</'.$tag.'>';
	}
}