<?php
namespace Asgard\Hook;

use Jeremeamia\SuperClosure\SerializableClosure;

/**
 * The hooks manager.
 * @author Michel Hognerud <michel@hognerud.net>
*/
class HooksManager implements HooksManagerInterface {
	use \Asgard\Container\ContainerAwareTrait;

	/**
	 * Static instance.
	 * @var HooksManagerInterface
	 */
	protected static $instance;
	/**
	 * Hooks registry.
	 * @var array
	 */
	protected $registry = [];

	/**
	 * Return a static instance.
	 * @return HooksManagerInterface
	 */
	public static function singleton() {
		if(!static::$instance)
			static::$instance = new static;
		return static::$instance;
	}

	/**
	 * Constructor.
	 * @param \Asgard\Container\ContainerInterface $container Application container.
	*/
	public function __construct(\Asgard\Container\ContainerInterface $container=null) {
		$this->container = $container;
	}

	/**
	 * {@inheritDoc}
	*/
	public function trigger($name, array $args=[], $cb=null, &$chain=null) {
		$chain = new HookChain($this->container);

		$chain->setCalls(array_merge(
			$this->get($name.'.before'),
			$this->get($name.'.on'),
			$cb !== null ? [$cb]:[],
			$this->get($name.'.after')
		));

		return $chain->run($args);
	}

	/**
	 * {@inheritDoc}
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
	 * @param callable $cb
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
	 * {@inheritDoc}
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
	 * @param callable $cb
	 * @param string   $type   on|before|after
	*/
	protected function createhook($identifier, $cb, $type='on') {
		$identifier .= '.'.$type;

		$this->set($identifier, $cb);
	}

	/**
	 * {@inheritDoc}
	*/
	public function hook($identifier, $cb) {
		$this->createhook($identifier, $cb, 'on');
	}

	/**
	 * {@inheritDoc}
	*/
	public function hookBefore($identifier, $cb) {
		$this->createhook($identifier, $cb, 'before');
	}

	/**
	 * {@inheritDoc}
	*/
	public function hookAfter($identifier, $cb) {
		$this->createhook($identifier, $cb, 'after');
	}

	/**
	 * {@inheritDoc}
	*/
	public function hooks(array $hooks) {
		foreach($hooks as $name=>$_hooks) {
			foreach($_hooks as $cb)
				$this->createhook($name, $cb);
		}
	}
}
