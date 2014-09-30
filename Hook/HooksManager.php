<?php
namespace Asgard\Hook;

use Jeremeamia\SuperClosure\SerializableClosure;

/**
 * The hooks manager.
 * @author Michel Hognerud <michel@hognerud.net>
*/
class HooksManager {
	use \Asgard\Container\ContainerAwareTrait;

	/**
	 * Static instance.
	 * @var HooksManager
	 */
	protected static $instance;
	/**
	 * Hooks registry.
	 * @var array
	 */
	public $registry = [];
	
	/**
	 * Return a static instance.
	 * @return HooksManager
	 */
	public static function singleton() {
		if(!static::$instance)
			static::$instance = new static;
		return static::$instance;
	}

	/**
	 * Constructor.
	 * @param \Asgard\Container\Container $container Application container.
	*/
	public function __construct($container=null) {
		$this->container = $container;
	}

	/**
	 * Trigger a hook.
	 * @param string    $name
	 * @param array     $args
	 * @param Callable  $cb Default callback.
	 * @param HookChain $chain
	 * @param HooksChain
	*/
	public function trigger($name, array $args=[], $cb=null, &$chain=null) {
		$chain = new HookChain($this->container);

		$chain->calls = array_merge(
			$this->get($name.'.before'),
			$this->get($name.'.on'),
			$cb !== null ? [$cb]:[],
			$this->get($name.'.after')
		);

		return $chain->run($args);
	}
	
	/**
	 * Check if a hook is present.
	 * @param string   $identifier
	 * @return boolean
	*/
	public function has($identifier) {
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
	 * Set a hook.
	 * @param string   $identifier Hook identifier.
	 * @param Callable $cb
	 * @param integer  $priority Hook priority in the list.
	*/
	protected function set($identifier, $cb, $priority=0) {
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
	 * Return hooks.
	 * @param string $identifier Hook identifier.
	 * @return array Callbacks.
	*/
	public function get($identifier) {
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
	 * Create a hook.
	 * @param string   $identifier
	 * @param Callable $cb
	 * @param string   $type   on|before|after
	*/
	protected function createhook($identifier, $cb, $type='on') {
		$identifier .= '.'.$type;

		$this->set($identifier, $cb);
	}
	
	/**
	 * Set a hook.
	 * @param string   $identifier
	 * @param Callable $cb
	*/
	public function hook($identifier, $cb) {
		$this->createhook($identifier, $cb, 'on');
	}
	
	/**
	 * Set a "before" hook.
	 * @param string   $identifier
	 * @param Callable $cb
	*/
	public function hookBefore($identifier, $cb) {
		$this->createhook($identifier, $cb, 'before');
	}
	
	/**
	 * Set an "after" hook.
	 * @param string   $identifier
	 * @param Callable $cb
	*/
	public function hookAfter($identifier, $cb) {
		$this->createhook($identifier, $cb, 'after');
	}
	
	/**
	 * Set multiple hooks.
	 * @param array $hooks
	*/
	public function hooks(array $hooks) {
		foreach($hooks as $name=>$_hooks) {
			foreach($_hooks as $cb)
				$this->createhook($name, $cb);
		}
	}
}
