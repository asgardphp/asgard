<?php
namespace Asgard\Http;

/**
 * Session manager.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class SessionManager implements \Asgard\Common\BagInterface {
	/**
	 * Constructor.
	 */
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

	/**
	 * Return all session variables.
	 * @return array
	 */
	public function all() {
		return $_SESSION;
	}

	/**
	 * Clear all session variables.
	 * @return SessionManager $this
	 */
	public function clear() {
		$_SESSION = [];
		return $this;
	}

	/**
	 * Return the number of session variables.
	 * @return integer
	 */
	public function size() {
		return count($_SESSION);
	}

	/**
	 * Set all session variables.
	 * @param array $data
	 */
	public function setAll($data) {
		$this->clear()->set($data);
	}

	/**
	 * Check if the session has a variable.
	 * @param  string  $path
	 * @return boolean
	 */
	public function has($path) {
		return \Asgard\Common\ArrayUtils::_isset($_SESSION, $path);
	}

	/**
	 * Delete a session variable.
	 * @param  string $path
	 */
	public function delete($path) {
		\Asgard\Common\ArrayUtils::_unset($_SESSION, $path);
	}

	/**
	 * Get a session variable.
	 * @param  string $path
	 * @return mixed
	 */
	public function get($path, $default=null) {
		if(!$this->has($path))
			return $default;
		return \Asgard\Common\ArrayUtils::get($_SESSION, $path);
	}

	/**
	 * Set a session variable.
	 * @param array|string $path
	 * @param mixed $value
	 */
	public function set($path, $value=null) {
		#to set multiple variables at once.
		if(is_array($path)) {
			foreach($path as $k=>$v)
				static::set($k, $v);
		}
		else
			\Asgard\Common\ArrayUtils::set($_SESSION, $path, $value);
	}

	/**
	 * Array set implementation.
	 * @param  string $offset
	 * @param  mixed $value
	 * @throws \LogicException If offset is null.
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
	 * @return boolean
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