<?php
namespace Asgard\Form;

class HTMLHelper {
	public static function tag($tag, $attrs, $inner=null) {
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