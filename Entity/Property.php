<?php
namespace Asgard\Entity;

use Jeremeamia\SuperClosure\SerializableClosure;

/**
 * Entity definition property.
 */
class Property {
	/**
	 * Entity definition.
	 * @var EntityDefinition
	 */
	protected $definition;
	/**
	 * Property position.
	 * @var integer
	 */
	protected $position;
	/**
	 * Property name.
	 * @var string
	 */
	protected $name;
	/**
	 * Parameters.
	 * @var array
	 */
	public $params = [];

	/**
	 * Constructor.
	 * @param array $params
	 */
	public function __construct(array $params) {
		$this->params = $params;
	}

	/**
	 * __sleep magic method.
	 * @return array
	 */
	public function __sleep() {
		foreach($this->params as $k=>$v) {
			if($v instanceof \Closure)
				$this->params[$k] = new SerializableClosure($v);
		}
		return ['position', 'definition', 'name', 'params'];
	}

	/**
	 * Set property position.
	 * @param integer $position
	 */
	public function setPosition($position) {
		$this->params['position'] = $position;
		return $this;
	}

	/**
	 * Get the property position.
	 * @return integer
	 */
	public function getPosition() {
		return isset($this->params['position']) ? $this->params['position']:null;
	}

	/**
	 * Set the name.
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Set the definition.
	 * @param EntityDefinition $definition
	 */
	public function setDefinition($definition) {
		$this->definition = $definition;
	}

	/**
	 * Check if entity is required.
	 * @return boolean
	 */
	public function required() {
		if(isset($this->params['required']))
			return !!$this->params['required'];
		if(isset($this->params['validation']['required']))
			return !!$this->params['validation']['required'];
	}

	/**
	 * __get magic method.
	 * @param  string $what
	 * @return mixed
	 */
	public function __get($what) {
		return $this->get($what);
	}

	/**
	 * Get a parameter.
	 * @param  string $path
	 * @param  mixed  $default
	 * @return mixed
	 */
	public function get($path, $default=null) {
		if(!$this->has($path))
			return $default;
		return \Asgard\Common\ArrayUtils::string_array_get($this->params, $path);
	}

	/**
	 * Check if has a parameter.
	 * @param  string  $path
	 * @return boolean
	 */
	public function has($path) {
		return \Asgard\Common\ArrayUtils::string_array_isset($this->params, $path);
	}

	/**
	 * Return parameters.
	 * @return array
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * Return the name.
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * __toString magic method.
	 * @return string
	 */
	public function __toString() {
		return $this->getName();
	}

	/**
	 * Return the default value.
	 * @param  Entity $entity
	 * @param  strng  $name
	 * @return mixed
	 */
	public function getDefault($entity, $name) {
		if($this->get('many'))
			return new ManyCollection($this->definition, $entity, $name);
		elseif(isset($this->params['default'])) {
			if(is_callable($this->params['default']))
				return $this->params['default']();
			else
				return $this->params['default'];
		}
		else
			return $this->_getDefault($entity);
	}

	/**
	 * Return the default value for a single element.
	 * @return null
	 */
	protected function _getDefault() {
		return null;
	}

	/**
	 * Return the validation rules.
	 * @return array
	 */
	public function getRules() {
		$res = isset($this->params['validation']) ? $this->params['validation']:[];
		if(!is_array($res))
			$res = [$res];
		if($this->get('required'))
			$res['required'] = true;

		return $res;
	}

	/**
	 * Return the validation messages.
	 * @return array
	 */
	public function getMessages() {
		if(isset($this->params['messages']))
			return $this->params['messages'];
	}

	/**
	 * Serialize the value.
	 * @param  mixed $val
	 * @return string
	 */
	public function serialize($val) {
		if($this->get('many')) {
			if(!$val instanceof ManyCollection)
				return serialize([]);
			$r = [];
			foreach($val as $v) {
				$s = $this->doSerialize($v);
				if($s !== null)
					$r[] = $s;
			}
			return serialize($r);
		}
		else
			return $this->doSerialize($val);
	}

	/**
	 * Actually perform serialization for a single element.
	 * @param  mixed $val
	 * @return string
	 */
	protected function doSerialize($val) {
		if(is_string($val) || is_numeric($val) || is_bool($val) || is_null($val))
			return $val;
		else
			return serialize($val);
	}

	/**
	 * Unserialize a string.
	 * @param  string $str
	 * @param  Entity $entity
	 * @param  string $name
	 * @return mixed
	 */
	public function unserialize($str, $entity, $name) {
		if($this->get('many')) {
			$r = new ManyCollection($this->definition, $entity, $name);
			$arr = unserialize($str);
			if(!is_array($arr))
				return $r;
			foreach($arr as $v)
				$r[] = $this->doUnserialize($v, $entity);
			return $r;
		}
		else
			return $this->doUnserialize($str, $entity);
	}

	/**
	 * Actually perform unserialization for a single element.
	 * @param  string $str
	 * @return mixed
	 */
	protected function doUnserialize($str) {
		$json = json_decode($str);
		if($json === null)
			return $str;
		return $json;
	}

	/**
	 * Pre-process value before passing it to entity.
	 * @param mixed  $val
	 * @param Entity $entity
	 * @param string $name
	 * @return mixed
	 */
	public function set($val, Entity $entity, $name) {
		if($this->get('many')) {
			if($val instanceof ManyCollection)
				return $val;
			if(is_array($val)) {
				$res = new ManyCollection($this->definition, $entity, $name);
				foreach($val as $v)
					$res[] = $this->doSet($v, $entity, $name);
				return $res;
			}
			else
				return $val;
		}
		else
			return $this->doSet($val, $entity, $name);
	}

	/**
	 * Actually pre-process value for a single element.
	 * @param  mixed  $val
	 * @param  Entity $entity
	 * @param  string $name
	 * @return mixed
	 */
	public function doSet($val, Entity $entity, $name) {
		return $val;
	}
}