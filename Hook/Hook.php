<?php
namespace Asgard\Hook;

class Hook {
	public $registry = array();
	protected $app;

	public function __construct($app) {
		$this->app = $app;
	}

	public function trigger_print($name, array $args=array(), $cb=null) {
		return $this->trigger($name, $args, $cb, true);
	}

	public function triggerChain_print(HookChain $chain, $name, array $args=array(), $cb=null) {
		return $this->triggerChain($chain, $name, $args, $cb, true);
	}

	public function trigger($name, array $args=array(), $cb=null, $print=false) {
		return $this->triggerChain(new HookChain($this->app), $name, $args, $cb, $print);
	}

	public function triggerChain(HookChain $chain, $name, array $args=array(), $cb=null, $print=false) {
		if(is_string($name))
			$name = explode('/', $name);

		$chain->calls = array_merge(
			$this->get(array_merge($name, array('before'))),
			$this->get(array_merge($name, array('on'))),
			$cb !== null ? array($cb):array(),
			$this->get(array_merge($name, array('after')))
		);

		return $chain->run($args, $print);
	}

	public function getHooks(array $path) {
		$result = $this->registry;
		foreach($path as $key) {
			if(!isset($result[$key]))
				return null;
			else
				$result = $result[$key];
		}

		return $result;
	}

	public function has(array $path) {
		$result = $this->registry;
		foreach($path as $key) {
			if(!isset($result[$key]))
				return false;
			else
				$result = $result[$key];
		}
		
		return true;
	}

	protected function set(array $path, $cb, $priority=0) {
		$arr =& $this->registry;
		$key = array_pop($path);
		foreach($path as $next)
			$arr =& $arr[$next];
		while(isset($arr[$key][$priority]))
			$priority += 1;
		$arr[$key][$priority] = $cb;
	}
	
	public function get(array $path=array()) {
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

	public function hooks(array $allhooks) {
		foreach($allhooks as $name=>$hooks) {
			foreach($hooks as $cb)
				$this->createhook($name, $cb);
		}
	}
}
