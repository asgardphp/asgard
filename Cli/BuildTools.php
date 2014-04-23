<?php
class BuildTools {
	public static function outputPHP($value) {
		if(is_array($value) && $value === array_values($value)) {
			$res = 'array('."\n";
			foreach($value as $v)
				$res .= "\t".static::outputPHP($v).",\n";
			$res .= ')';
			return $res;
		}
		else
			return var_export($value, true);
	}
}