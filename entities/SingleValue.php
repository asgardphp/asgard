<?php
namespace Coxis\Value\Entities;

abstract class SingleValue extends \Coxis\Core\Entity {
	public static $stored = array();

	public static $meta = array(
		'table'	=>	'value',
	);

	public function __toString() {
		return $this->key;
	}
	
	public static function fetch($name) {
		if(isset(static::$stored[$name]))
			$value = static::$stored[$name];
		else
			static::$stored[$name] = $value = static::loadByKey($name);
		if(!$value)
			static::$stored[$name] = $value = static::create(array('key'=>$name));
		
		return $value;
	}

	public static function val($name, $val=null) {
		if($val===null)
			return static::_get($name);
		else
			return static::_set($name, $val);
	}

	public static function _get($name) {
		return static::fetch($name)->value;
	}

	protected static function _set($name, $val) {
		return static::fetch($name)->save(array('value'=>$val));
	}

	public static function rawVal($name) {
		return static::fetch($name)->raw('value');
	}

	public static function configure($definition) {
		$definition->value->params['type'] = 'text';
	}
}