<?php
namespace Asgard\Http;

class CookieManager implements \ArrayAccess {
	public function all() {
		return $_COOKIE;
	}

	public function clear() {
		$_COOKIE = [];
		return $this;
	}

	public function size() {
		return count($_COOKIE);
	}

	public function setAll($data) {
		$this->clear()->set($data);
	}
	
	public function has($path) {
		return isset($_COOKIE[$path]);
	}

	public function get($path) {
		if(!$this->has($path)) return;
		return $_COOKIE[$path];
	}

	public function set($what, $value=null, $time=null, $path='/') {
		if(is_array($what)) {
			foreach($what as $k=>$v)
				static::set($k, $v);
		}
		else {
			if(!headers_sent()) {
				if($time === null)
					$time = time()+3600*24*365;
				setcookie($what, $value, $time, $path);
			}
		}
	}
	  
	public function delete($path, $_path='/') {
		if(!headers_sent())
			setcookie($path, false, -10000, $_path);
		unset($_COOKIE[$path]);
	}

    public function offsetSet($offset, $value) {
        if(is_null($offset))
            throw new \LogicException('Offset must not be null.');
        else
       		$this->set($offset, $value);
    }

    public function offsetExists($offset) {
        return $this->has($offset);
    }

    public function offsetUnset($offset) {
        return $this->delete($offset);
    }

    public function offsetGet($offset) {
        return $this->get($offset);
    }
}