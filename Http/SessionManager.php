<?php
namespace Asgard\Http;

class SessionManager implements \ArrayAccess {
	public function __construct() {
		if(headers_sent())
			return;
		if(isset($_SERVER['PHPSESSID']))
			session_id($_SERVER['PHPSESSID']);
		elseif(isset($_POST['PHPSESSID']))
			session_id($_POST['PHPSESSID']);
		elseif(isset($_GET['PHPSESSID']))
			session_id($_GET['PHPSESSID']);
		session_start();
	}

	public function all() {
		return $_SESSION;
	}

	public function clear() {
		$_SESSION = array();
		return $this;
	}

	public function size() {
		return count($_SESSION);
	}

	public function setAll($data) {
		$this->clear()->set($data);
	}
	
	public function has($path) {
		return \Asgard\Utils\Tools::string_array_isset($_SESSION, $path);
	}
	
	public function delete($path) {
		return \Asgard\Utils\Tools::string_array_unset($_SESSION, $path);
	}

	public function get($path) {
		return \Asgard\Utils\Tools::string_array_get($_SESSION, $path);
	}

	public function set($path, $value=null) {
		if(is_array($path)) {
			foreach($path as $k=>$v)
				static::set($k, $v);
		}
		else {
			\Asgard\Utils\Tools::string_array_set($_SESSION, $path, $value);
		}
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