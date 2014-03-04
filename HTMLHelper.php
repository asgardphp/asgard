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

		if(is_callable($inner))
			$str .= $inner();
		else
			$str .= $inner;

		$str .= '</'.$tag.'>';
		
		return $str;
	}
}