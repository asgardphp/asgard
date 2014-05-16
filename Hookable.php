<?php
namespace Asgard\Hook;

class Hookable {
	/* INSTANCE */
	public function hasHook($name) {
		return \Asgard\Core\App::get('hook')->has(array('instances', spl_object_hash($this), $name));
	}

	public function trigger($name, array $args=array(), $cb=null, $print=false) {
		return \Asgard\Core\App::get('hook')->trigger(array('instances', spl_object_hash($this), $name), $args, $cb, $print);
	}

	public function triggerChain(HookChain $chain, $name, array $args=array(), $cb=null, $print=false) {
		return \Asgard\Core\App::get('hook')->triggerChain($chain, array('instances', spl_object_hash($this), $name), $args, $cb, $print);
	}

	public function hook() {
		return call_user_func_array(array($this, 'hookOn'), func_get_args());
	}

	public function hookOn($hookName, $cb, $priority=0) {
		$args = array(array('instances', spl_object_hash($this), $hookName), $cb);
		return call_user_func_array(array(\Asgard\Core\App::get('hook'), 'hookOn'), $args);
	}

	public function hookBefore($hookName, $cb, $priority=0) {
		$args = array(array('instances', spl_object_hash($this), $hookName), $cb);
		return call_user_func_array(array(\Asgard\Core\App::get('hook'), 'hookBefore'), $args);
	}

	public function hookAfter($hookName, $cb, $priority=0) {
		$args = array(array('instances', spl_object_hash($this), $hookName), $cb);
		return call_user_func_array(array(\Asgard\Core\App::get('hook'), 'hookAfter'), $args);
	}

	public function getHooks() {
		return \Asgard\Core\App::get('hook')->getHooks(array('instances', spl_object_hash($this)));
	}
}
