<?php
namespace Asgard\Core;

/**
 * Asgard kernel class.
 */
class Kernel implements \ArrayAccess {
	use \Asgard\Container\ContainerAwareTrait;

	const VERSION = 0.2;
	/**
	 * Kernel parameters.
	 * @var array
	 */
	protected $params = [];
	/**
	 * Config instance.
	 * @var \Asgard\Config\Config
	 */
	protected $config;
	/**
	 * User-added bundles.
	 * @var array
	 */
	protected $addedBundles = [];
	/**
	 * All bundles.
	 * @var array
	 */
	protected $bundles;
	/**
	 * Check if kernel was already loaded.
	 * @var boolean
	 */
	protected $loaded = false;

	/**
	 * Constructor.
	 * @param string $root
	 * @param string $env 
	 */
	public function __construct($root=null, $env=null) {
		$this->setRoot($root);
		$this->setEnv($env);
	}

	/**
	 * Get the services container.
	 * @return \Asgard\Container\Container
	 */
	public function getContainer() {
		if(!$this->container) {
			$this->container = $this->buildContainer($this->getConfig()['cache']);
			$this->container['kernel'] = $this;
		}
		return $this->container;
	}

	/**
	 * Set the config dependency.
	 * @param \Asgard\Config\Config $config
	 */
	public function setConfig($config) {
		$this->config = $config;
		return $this;
	}

	/**
	 * Get the config dependency.
	 * @return \Asgard\Config\Config
	 */
	public function getConfig() {
		if(!$this->config) {
			$this->config = $config = new \Asgard\Config\Config();
			$config->loadConfigDir($this['root'].'/config', $this->getEnv());
		}
		return $this->config;
	}

	/**
	 * Set the application root.
	 * @param string $root
	 */
	public function setRoot($root) {
		$this->params['root'] = $root;
		return $this;
	}

	/**
	 * Set the enviornment.
	 * @param string $env
	 */
	public function setEnv($env) {
		$this->params['env'] = $env;
		return $this;
	}

	/**
	 * Get the environment.
	 * @return string
	 */
	public function getEnv() {
		if(!isset($this->params['env']))
			$this->setDefaultEnvironment();
		return $this->params['env'];
	}

	/**
	 * Load the kernel.
	 */
	public function load() {
		if($this->loaded)
			return;

		if($this->getEnv() == 'prod' && file_exists($this['root'].'/storage/compiled.php'))
			include_once $this['root'].'/storage/compiled.php';

		$this->bundles = $this->doGetBundles($this->getConfig()['cache']);
		$container = $this->getContainer();

		$container['config'] = $this->getConfig();

		if($this['env']) {
			if(file_exists($this['root'].'/app/bootstrap_'.strtolower($this['env']).'.php'))
				include $this['root'].'/app/bootstrap_'.strtolower($this['env']).'.php';
		}
		if(file_exists($this['root'].'/app/bootstrap_all.php'))
			include $this['root'].'/app/bootstrap_all.php';

		$this->runBundles();

		$this->loaded = true;

		return $this;
	}

	/**
	 * Set the default enviornment.
	 */
	protected function setDefaultEnvironment() {
		#Using _ENV_ and $_SERVER only as the last chance to guess the environment.
		#User can and should set the environment through constructor or setEnv($env).

		if(isset($this['env']))
			return;
		if(defined('_ENV_'))
			$this['env'] = _ENV_;
		elseif($this['consoleMode']) {
			foreach($_SERVER['argv'] as $k=>$v) {
				if($v === '--env' && isset($_SERVER['argv'][$k+1])) {
					$this['env'] = $_SERVER['argv'][$k+1];
					return;
				}
			}
			$this['env'] = 'dev';
		}
		elseif(isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] == '127.0.0.1' || $_SERVER['HTTP_HOST'] == 'localhost'))
			$this['env'] = 'dev';
		else
			$this['env'] = 'prod';
	}

	/**
	 * Get the cache dependency.
	 * @param  string $cache
	 * @return \Asgard\Cache\Cache
	 */
	protected function getCache($cache) {
		$reflector = new \ReflectionClass($cache);
		return $reflector->newInstanceArgs([$this['root'].'/storage/cache/']);
	}

	/**
	 * Register the bundle's services.
	 * @param  boolean|string $cache
	 */
	protected function buildContainer($cache=false) {
		if($cache) {
			$c = $this->getCache($cache);
			if(($this->container = $c->fetch('app')) !== false) {
				#make $this->container the default instance of Container, in case someones uses it
				\Asgard\Container\Container::setInstance($this->container);
				return $this->container;
			}
		}

		$bundles = $this->getAllBundles();
		#use the Container default instance, in case someones uses it
		$container = $this->container = \Asgard\Container\Container::singleton();

		foreach($bundles as $bundle)
			$bundle->buildContainer($container);

		if($cache)
			$c->save('app', $container);

		return $container;
	}

	/**
	 * Run the bundles.
	 */
	protected function runBundles() {
		$bundles = $this->getAllBundles();

		foreach($bundles as $bundle)
			$bundle->run($this->container);
	}

	/**
	 * Get all the bundles.
	 * @return array
	 */
	public function getAllBundles() {
		if($this->bundles === null)
			$this->bundles = $this->doGetBundles();
		return $this->bundles;
	}

	/**
	 * Get the hooks annotations reader dependency.
	 * @param  boolean|string $cache
	 * @return \Asgard\Hook\AnnotationsReader
	 */
	protected function getHooksAnnotationsReader($cache) {
		$annotationsReader = new \Asgard\Hook\AnnotationsReader();
		if($cache)
			$annotationsReader->setCache($this->getCache($cache));
		$annotationsReader->setDebug($this->getConfig()['debug']);
		return $annotationsReader;
	}

	/**
	 * Actually fetch all the budles.
	 * @param  boolean|string $cache
	 * @return array
	 */
	protected function doGetBundles($cache=false) {
		if($cache) {
			$c = $this->getCache($cache);
			if(($res = $c->fetch('bundles')) !== false)
				return $res;
		}

		$bundles = array_merge($this->addedBundles, $this->getBundles());

		$newBundles = false;
		foreach($bundles as $k=>$v) {
			if(is_string($v)) {
				$bundle = realpath($v);
				if(!$bundle)
					throw new \Exception('Bundle '.$v.' does not exist.');
				unset($bundles[$k]);
				$bundles[$bundle] = null;
				$newBundles = true;

				if(file_exists($bundle.'/Bundle.php'))
					require_once $bundle.'/Bundle.php';
			}
		}
		if($newBundles) {
			foreach(get_declared_classes() as $class) {
				if(!is_subclass_of($class, 'Asgard\Core\BundleLoader'))
					continue;
				$reflector = new \ReflectionClass($class);
				$dir = dirname($reflector->getFileName());
				if(array_key_exists($dir, $bundles) && $bundles[$dir] === null) {
					unset($bundles[$dir]);
					$bundles[] = new $class;
				}
			}
		}
		foreach($bundles as $bundle=>$obj) {
			if($obj === null) {
				$obj = new BundleLoader;
				$obj->setPath($bundle);
				$bundles[$bundle] = $obj;
			}
		}

		#Remove duplicates
		foreach($bundles as $k=>$b) {
			for($i=$k+1; isset($bundles[$i]); $i++) {
				if($b->getPath() === $bundles[$i]->getPath())
					unset($bundles[$i]);
			}
		}

		if($cache)
			$c->save('bundles', $bundles);

		foreach($bundles as $bundle)
			$bundle->setHooksAnnotationsReader($this->getHooksAnnotationsReader($cache));

		return $bundles;
	}

	/**
	 * Get the default bundles.
	 * @return array
	 */
	protected function getBundles() {
		return [];
	}

	/**
	 * Add bundles.
	 * @param array $bundles
	 */
	public function addBundles($bundles) {
		$this->addedBundles = array_merge($this->addedBundles, $bundles);
	}

	/**
	 * Get the kernel version.
	 * @return float
	 */
	public static function getVersion() {
		return static::VERSION;
	}

	/**
	 * Array set implementation.
	 * @param  integer $offset
	 * @param  mixed $value
	 * @throws \LogicException If $offset is null
	 */
	public function offsetSet($offset, $value) {
		if(is_null($offset))
			throw new \LogicException('Offset must not be null.');
		else
			$this->params[$offset] = $value;
	}

	/**
	 * Array exists implementation.
	 * @param  integer $offset
	 * @return boolean true if exists
	 */
	public function offsetExists($offset) {
		return isset($this->params[$offset]);
	}

	/**
	 * Array unset implementation.
	 * @param  integer $offset
	 */
	public function offsetUnset($offset) {
		unset($this->params[$offset]);
	}

	/**
	 * Array get implementation.
	 * @param  integer $offset
	 * @return mixed
	 */
	public function offsetGet($offset) {
		if(!isset($this->params[$offset]))
			return;
		return $this->params[$offset];
	}
}