<?php
namespace Asgard\Hook;

class Hook {
	public $registry = array();

	public function trigger_print($name, $args=array(), $cb=null) {
		return $this->trigger($name, $args, $cb, true);
	}

	public function triggerChain_print($chain, $name, $args=array(), $cb=null) {
		return $this->triggerChain($chain, $name, $args, $cb, true);
	}

	public function trigger($name, $args=array(), $cb=null, $print=false) {
		return $this->triggerChain(new \Asgard\Hook\HookChain, $name, $args, $cb, $print);
	}

	public function triggerChain($chain, $name, $args=array(), $cb=null, $print=false) {
		if(is_string($name))
			$name = explode('/', $name);

		$chain->calls = array_merge(
			$this->get(array_merge($name, array('before'))),
			$cb !== null ? array($cb):array(),
			$this->get(array_merge($name, array('on'))),
			$this->get(array_merge($name, array('after')))
		);
		
		if(!is_array($args))
			$args = array($args);

		return $chain->run($args, $print);
	}

	public function getHooks($path) {
		$result = $this->registry;
		foreach($path as $key)
			if(!isset($result[$key]))
				return false;
			else
				$result = $result[$key];
		return $result;
	}

	public function has($path) {
		$result = $this->registry;
		foreach($path as $key)
			if(!isset($result[$key]))
				return false;
			else
				$result = $result[$key];
		
		return true;
	}

	protected function set($path, $cb, $priority=0) {
		$arr =& $this->registry;
		$key = array_pop($path);
		foreach($path as $next)
			$arr =& $arr[$next];
		while(isset($arr[$key][$priority]))
			$priority += 1;
		$arr[$key][$priority] = $cb;
	}
	
	public function get($path=array()) {
		$result = $this->registry;
		foreach($path as $key)
			if(!isset($result[$key]))
				return array();
			else
				$result = $result[$key];
		
		return $result;
	}

	protected function createhook($name, $cb, $type='on') {
		if(is_string($name))
			$name = explode('/', $name);
		$name[] = $type;

		$this->set($name, $cb);
	}

	public function hook() {
		if(!func_get_args())
			return;
		return call_user_func_array(array(get_called_class(), 'hookOn'), func_get_args());
	}

	public function hookOn($hookName, $cb, $priority=0) {
		$this->createhook($hookName, $cb, 'on');
	}

	public function hookBefore($hookName, $cb, $priority=0) {
		$this->createhook($hookName, $cb, 'before');
	}

	public function hookAfter($hookName, $cb, $priority=0) {
		$this->createhook($hookName, $cb, 'after');
	}

	public function hooks($allhooks) {
		foreach($allhooks as $name=>$hooks)
			foreach($hooks as $cb)
				$this->createhook($name, $cb);
	}
}
