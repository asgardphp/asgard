<?php
namespace Asgard\Core\Filters;
class PageCaching extends Filter {
	public function getAfterPriority() {
		return 1000;
	}

	public function before($chain) {
		$key = $this->key = $this->calculateKey();
		if($r = \Asgard\Utils\Cache::get($key))
			return $r;
	}

	public function after($chain, $controller, $result) {
		\Asgard\Utils\Cache::set($this->key, $result);
	}

	protected function calculateKey() {
		$key = $this->controller->request['controller'].$this->controller->request['action'];
		#todo varyBy...

		return md5($key);
	}
}