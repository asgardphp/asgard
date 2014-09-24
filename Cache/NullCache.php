<?php
namespace Asgard\Cache;

/**
 * Fake cache class. Uses the same API but does not persist objects.
 */
class NullCache implements \Doctrine\Common\Cache\Cache {
	/**
	 * Return the default value.
	 * @param  string  $id
	 * @param  mixed   $default
	 * @return mixed
	 */
	public function fetch($id, $default=false) {
		if(is_callable($default))
			return $default();
		return $default;
	}

	/**
	 * Always return false as NullCache can't contain values.
	 * @param  string $id
	 * @return boolean false
	 */
	public function contains($id) {
		return false;
	}

	/**
	 * Do nothing.
	 * @param  string  $id
	 * @param  mixed   $data
	 * @param  integer $lifeTime
	 * @return boolean false
	 */
	public function save($id, $data, $lifeTime=0) {
		return false;
	}

	/**
	 * Do nothing.
	 * @param  string $id
	 * @return boolean false
	 */
	public function delete($id) {
		return false;
	}

	/**
	 * Return nothing.
	 * @return null
	 */
	public function getStats() {
		return null;
	}
}