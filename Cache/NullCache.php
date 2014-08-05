<?php
namespace Asgard\Cache;

class NullCache implements \Doctrine\Common\Cache\Cache {
	/**
	 * {@inheritdoc}
	 */
	public function fetch($id, $default=false) {
		if(is_callable($default))
			return $default();
		return $default;
	}

	/**
	 * {@inheritdoc}
	 */
	public function contains($id) {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save($id, $data, $lifeTime=0) {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete($id) {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getStats() {
		return null;
	}
}