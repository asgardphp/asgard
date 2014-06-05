<?php
namespace Asgard\Hook;

use Jeremeamia\SuperClosure\SerializableClosure;

/**
 * The hooks manager.
 * 
 * @author Michel Hognerud <michel@hognerud.net>
*/
class HooksManager {
	public $registry = array();
	protected $app;
	
	/**
	 * Constructor.
	 * 
	 * @param \Asgard\Core\App app Application container.
	*/
	public function __construct($app=null) {
		$this->app = $app;
	}

	/**
	 * Triggers a hook.
	 * 
	 * @param string name
	 * @param array args
	 * @param Callback cb Default callback.
	 * 
	 * @api 
	*/
	public function trigger($name, array $args=array(), $cb=null) {
		return $this->triggerChain(new HookChain($this->app), $name, $args, $cb);
	}
	
	/**
	 * Triggers a hook with a given chain.
	 * 
	 * @param HookChain chain
	 * @param string name
	 * @param array args
	 * @param Callback cb Default callback.
	 * 
	 * @api 
	*/
	public function triggerChain(HookChain $chain, $name, array $args=array(), $cb=null) {
		if(is_string($name))
			$name = explode('.', $name);

		$chain->calls = array_merge(
			$this->get(array_merge($name, array('before'))),
			$this->get(array_merge($name, array('on'))),
			$cb !== null ? array($cb):array(),
			$this->get(array_merge($name, array('after')))
		);

		return $chain->run($args);
	}
	
	/**
	 * Checks if a hook is present.
	 * 
	 * @param string identifier
	 * 
	 * @return boolean
	 * 
	 * @api 
	*/
	public function has($identifier) {
		if(is_string($identifier))
			$identifier = explode('.', $identifier);
		$result = $this->registry;
		foreach($identifier as $key) {
			if(!isset($result[$key]))
				return false;
			else
				$result = $result[$key];
		}
		
		return true;
	}
	
	/**
	 * Sets a hook.
	 * 
	 * @param string identifier Hook identifier.
	 * @param Callback cb
	 * @param integer priority Hook priority in the list.
	*/
	protected function set($identifier, $cb, $priority=0) {
		if(is_string($identifier))
			$identifier = explode('.', $identifier);
		$arr =& $this->registry;
		$key = array_pop($identifier);
		foreach($identifier as $next)
			$arr =& $arr[$next];
		while(isset($arr[$key][$priority]))
			$priority += 1;
		if($cb instanceof \Closure)
			$cb = new SerializableClosure($cb);
		$arr[$key][$priority] = $cb;
	}
	
	/**
	 * Returns hooks.
	 * 
	 * @param string identifier Hook identifier.
	 * 
	 * @return array Callbacks.
	*/
	public function get($identifier) {
		if(is_string($identifier))
			$identifier = explode('.', $identifier);
		$last = array_pop($identifier);
		$result =& $this->registry;
		foreach($identifier as $key) {
			if(!isset($result[$key]))
				return array();
			else
				$result =& $result[$key];
		}
		
		if(isset($result[$last]))
			return $result[$last];
		else
			return array();
	}
	
	/**
	 * Creates a hook.
	 * 
	 * @param string identifier
	 * @param Callback. cb
	 * @param before|on|after type
	*/
	protected function createhook($identifier, $cb, $type='on') {
		if(is_string($identifier))
			$identifier = explode('.', $identifier);
		$identifier[] = $type;

		$this->set($identifier, $cb);
	}
	
	/**
	 * Sets a hook.
	 * 
	 * @param string identifier
	 * @param Callback cb
	 * @param integer priority Hook priority in the list.
	 * 
	 * @api 
	*/
	public function hook($identifier, $cb, $priority=0) {
		$this->createhook($identifier, $cb, 'on');
	}
	
	/**
	 * Sets a "before" hook.
	 * 
	 * @param string identifier
	 * @param Callback cb
	 * @param integer priority Hook priority in the list.
	 * 
	 * @api 
	*/
	public function hookBefore($identifier, $cb, $priority=0) {
		$this->createhook($identifier, $cb, 'before');
	}
	
	/**
	 * Sets an "after" hook.
	 * 
	 * @param string identifier
	 * @param Callback cb
	 * @param integer priority Hook priority in the list.
	 * 
	 * @api 
	*/
	public function hookAfter($identifier, $cb, $priority=0) {
		$this->createhook($identifier, $cb, 'after');
	}
	
	/**
	 * Sets multiple hooks.
	 * 
	 * @param array allhooks
	 * 
	 * @api 
	*/
	public function hooks(array $allhooks) {
		foreach($allhooks as $name=>$hooks) {
			foreach($hooks as $cb)
				$this->createhook($name, $cb);
		}
	}
}
