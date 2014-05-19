<?php
namespace Asgard\Core;

class App implements \ArrayAccess {
	protected static $instance;
	protected $instances = array();
	protected $registry = array();
	protected $loaded = false;

	public function __construct($config=null) {
		$this->config = $config;
	}

	public static function hasInstance() {
		return isset(static::$instance);
	}

	public static function instance($new=false, $config=null) {
		if(!isset(static::$instance) || $new)
			static::$instance = new static($config);
		return static::$instance;
	}

	public function get($class) {
		if(!isset($this->instances[$class])) {
			if($this->registry[$class]['save']) {
				$this->instances[$class] = $this->make($class);
				return $this->instances[$class];
			}
			else
				return $this->make($class);
		}
		else
			return $this->instances[$class];
		// return $this->_get($class);
	}

	// public function _get($class) {
	// }

	// public function __get($name) {
	// 	return $this->_get($name);
	// }

	public function set($name, $value) {
			$this->instances[$name] = $value;
		// $instance = static::instance();
		// return $instance->_set($name, $value);
	}

	// public function _set($name, $value) {
	// 	// if(is_callable($value))
	// 	// 	$this->register($name, $value);
	// 	// else
	// 		$this->instances[$name] = $value;
	// 	return $this;
	// }

	// public function __set($name, $value) {
	// 	return $this->_set($name, $value);
	// }

	public static function has($class) {
		$instance = static::instance();
		return $instance->_has($class);
	}

	public function _has($class) {
		return $this->registered($class) || isset($this->instances[$class]);
	}

	public function register($name, $callback, $save=true) {
		if(
			$this->_has('config')
			&& $this->get('config')->get('autofacade')
			&& preg_match('/^[a-zA-Z0-9_]+$/', $name)
			&& !class_exists(ucfirst(strtolower($name)))) {
			eval('class '.ucfirst(strtolower($name)).' extends \Asgard\Core\Facade {}');
		}

		$this->registry[$name] = array('callback'=>$callback, 'save'=>$save);
	}
	
	public function make($name, array $params=array(), $default=null) {
		if(isset($this->registry[$name]))
			return call_user_func_array($this->registry[$name]['callback'], array($this, $params));
		else {
			if($default instanceof \Closure)
				return call_user_func_array($default, $params);
			else {
				if($default === null)
					throw new \Exception('There is no constructor for "'.$name.'".');
				return $default;
			}
		}
	}

	public function registered($name) {
		return isset($this->registry[$name]);
	}

    public function offsetSet($offset, $value) {
        if(is_null($offset))
            throw new \LogicException('Offset must not be null.');
        else
       		$this->set($offset, $value);
    }

    public function offsetExists($offset) {
        return isset($this->instances[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->instances[$offset]);
    }

    public function offsetGet($offset) {
        return $this->get($offset);
    }
}