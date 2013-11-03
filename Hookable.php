<?php
namespace Coxis\Hook;

class Hookable {
	/* INSTANCE */
	public function hasHook($name) {
		return \Hook::has(array('instances', spl_object_hash($this), $name));
	}

	public function trigger($name, $args=array(), $cb=null) {
		return \Hook::trigger(array('instances', spl_object_hash($this), $name), $args, $cb);
	}

	public function triggerChain($chain, $name, $args=array(), $cb=null) {
		return \Hook::triggerChain($chain, array('instances', spl_object_hash($this), $name), $args, $cb);
	}

	public function hook() {
		return call_user_func_array(array($this, 'hookOn'), func_get_args());
	}

	public function hookOn($hookName, $cb, $priority=0) {
		$args = array(array('instances', spl_object_hash($this), $hookName), $cb);
		return call_user_func_array(array('Hook', 'hookOn'), $args);
	}

	public function hookBefore($hookName, $cb, $priority=0) {
		$args = array(array('instances', spl_object_hash($this), $hookName), $cb);
		return call_user_func_array(array('Hook', 'hookBefore'), $args);
	}

	public function hookAfter($hookName, $cb, $priority=0) {
		$args = array(array('instances', spl_object_hash($this), $hookName), $cb);
		return call_user_func_array(array('Hook', 'hookAfter'), $args);
	}

	public function getHooks() {
		return \Hook::getHooks(array('instances', spl_object_hash($this)));
	}
}
