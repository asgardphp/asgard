<?php
namespace Asgard\Cache;

class NullCache implements \Doctrine\Common\Cache\Cache {
	public function fetch($id, $default=false) {
		if(is_callable($default))
			return $default();
		return $default;
	}

	public function contains($id) {
		return false;
	}

	public function save($id, $data, $lifeTime=0) {
		return false;
	}

	public function delete($id) {
		return false;
	}

	public function getStats() {
		return null;
	}
}