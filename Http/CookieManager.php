<?php
namespace Asgard\Http;

/**
 * To manage cookies.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class CookieManager implements \Asgard\Common\BagInterface {
	/**
	 * Return all cookies.
	 * @return array
	 */
	public function all() {
		return $_COOKIE;
	}

	/**
	 * Clear all cookies.
	 * @return CookieManager $this
	 */
	public function clear() {
		$_COOKIE = [];
		return $this;
	}

	/**
	 * Return the number of cookies.
	 * @return integer
	 */
	public function size() {
		return count($_COOKIE);
	}

	/**
	 * Set all cookies.
	 * @param array $data
	 */
	public function setAll($data) {
		$this->clear()->set($data);
	}

	/**
	 * Check if it contains a cookie.
	 * @param  string  $path
	 * @return boolean true if cookie exists.
	 */
	public function has($path) {
		return isset($_COOKIE[$path]);
	}

	/**
	 * Get a cookie.
	 * @param  string $path
	 * @return mixed
	 */
	public function get($path, $default=null) {
		if(!$this->has($path))
			return $default;
		return $_COOKIE[$path];
	}

	/**
	 * Set a cookie.
	 * @param array|string $what
	 * @param mixed $value
	 * @param integer $time
	 * @param string $path
	 */
	public function set($what, $value=null, $time=null, $path='/') {
		#set multiple elements at once
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

	/**
	 * Delete a cookie.
	 * @param  string $path
	 * @param  string $_path
	 */
	public function delete($path, $_path='/') {
		if(!headers_sent())
			setcookie($path, null, -10000, $_path);
		unset($_COOKIE[$path]);
	}

	/**
	 * Array set implementation.
	 * @param  string $offset
	 * @param  mixed   $value
	 * @throws \LogicException If $offset is null
	 */
	public function offsetSet($offset, $value) {
		if(is_null($offset))
			throw new \LogicException('Offset must not be null.');
		else
			$this->set($offset, $value);
	}

	/**
	 * Array exists implementation.
	 * @param  string $offset
	 * @return boolean true if cookie exists.
	 */
	public function offsetExists($offset) {
		return $this->has($offset);
	}

	/**
	 * Array unset implementation.
	 * @param  string $offset
	 */
	public function offsetUnset($offset) {
		$this->delete($offset);
	}

	/**
	 * Array get implementation.
	 * @param  string $offset
	 * @return mixed
	 */
	public function offsetGet($offset) {
		return $this->get($offset);
	}
}