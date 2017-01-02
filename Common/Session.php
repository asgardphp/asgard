<?php
namespace Asgard\Common;

/**
 * Session.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Session implements BagInterface {
	static protected $singleton;

	public static function singleton() {
		if(!static::$singleton)
			static::$singleton = new static;
		return static::$singleton;
	}

	public static function setSingleton($singleton) {
		static::$singleton = $singleton;
	}

	/**
	 * Constructor.
	 */
	public function __construct($session_id=null) {
		if($session_id === null)
			$session_id = static::getGlobalSessionId();
		if($session_id)
			session_id($session_id);
		session_start();
	}

	/**
	 * Return the global session id.
	 * @return string
	*/
	public static function getGlobalSessionId() {
		if(isset($_SERVER['PHPSESSID']))
			return $_SERVER['PHPSESSID'];
		elseif(isset($_POST['PHPSESSID']))
			return $_POST['PHPSESSID'];
		elseif(isset($_GET['PHPSESSID']))
			return $_GET['PHPSESSID'];
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
	 * @return static $this
	 */
	public function clear() {
		$_SESSION = [];
		return $this;
	}

	/**
	 * Return the number of session variables.
	 * @return integer
	 */
	public function count() {
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
		return ArrayUtils::has($_SESSION, $path);
	}

	/**
	 * Delete a session variable.
	 * @param  string $path
	 */
	public function delete($path) {
		ArrayUtils::delete($_SESSION, $path);
	}

	/**
	 * Get a session variable.
	 * @param  string $path
	 * @param  mixed  $default
	 * @return mixed
	 */
	public function get($path, $default=null) {
		if(!$this->has($path))
			return $default;
		return ArrayUtils::get($_SESSION, $path);
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
			ArrayUtils::set($_SESSION, $path, $value);
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