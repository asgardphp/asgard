<?php
namespace Asgard\Hook;

use Jeremeamia\SuperClosure\SerializableClosure;

/**
 * The hooks manager.
 * 
 * @author Michel Hognerud <michel@hognerud.net>
*/
class HooksManager {
	use \Asgard\Container\ContainerAwareTrait;

	protected static $instance;
	public $registry = [];
	
	public static function singleton() {
		if(!static::$instance)
			static::$instance = new static;
		return static::$instance;
	}

	/**
	 * Constructor.
	 * 
	 * @param \Asgard\Container\Container app Application container.
	*/
	public function __construct($container=null) {
		$this->container = $container;
	}

	/**
	 * Triggers a hook.
	 * 
	 * @param string name
	 * @param array args
	 * @param Callback cb Default callback.
	 * @param HooksChain
	 * 
	 * @api 
	*/
	public function trigger($name, array $args=[], $cb=null, &$chain=null) {
		$chain = new HookChain($this->container);
		if(is_string($name))
			$name = explode('.', $name);

		$chain->calls = array_merge(
			$this->get(array_merge($name, ['before'])),
			$this->get(array_merge($name, ['on'])),
			$cb !== null ? [$cb]:[],
			$this->get(array_merge($name, ['after']))
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
				return [];
			else
				$result =& $result[$key];
		}
		
		if(isset($result[$last]))
			return $result[$last];
		else
			return [];
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
	 * 
	 * @api 
	*/
	public function hook($identifier, $cb) {
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
	public function hookBefore($identifier, $cb) {
		$this->createhook($identifier, $cb, 'before');
	}
	
	/**
	 * Sets an "after" hook.
	 * 
	 * @param string identifier
	 * @param Callback cb
	 * 
	 * @api 
	*/
	public function hookAfter($identifier, $cb) {
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
