<?php
namespace Asgard\Core;

/**
 * Asgard kernel class.
 * @author Michel Hognerud <michel@hognerud.com>
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
	 * @var \Asgard\Config\ConfigInterface
	 */
	protected $config;
	/**
	 * Cache instance.
	 * @var \Asgard\Cache\CacheInterface
	 */
	protected $cache;
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
	 * @return \Asgard\Container\ContainerInterface
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
	 * @param \Asgard\Config\ConfigInterface $config
	 */
	public function setConfig(\Asgard\Config\ConfigInterface $config) {
		$this->config = $config;
		return $this;
	}

	/**
	 * Get the config dependency.
	 * @return \Asgard\Config\ConfigInterface
	 */
	public function getConfig() {
		if(!$this->config) {
			$this->config = $config = new \Asgard\Config\Config;
			if(file_exists($this->params['root'].'/config'))
				$config->loadDir($this->params['root'].'/config', $this->getEnv());
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

		if($this->getEnv() == 'prod' && file_exists($this->params['root'].'/storage/compiled.php'))
			include_once $this->params['root'].'/storage/compiled.php';

		$this->bundles = $this->doGetBundles();
		$container = $this->getContainer();

		$container['config'] = $this->getConfig();

		if($this->params['env']) {
			if(file_exists($this->params['root'].'/app/bootstrap_'.strtolower($this->params['env']).'.php'))
				include $this->params['root'].'/app/bootstrap_'.strtolower($this->params['env']).'.php';
		}
		if(file_exists($this->params['root'].'/app/bootstrap_all.php'))
			include $this->params['root'].'/app/bootstrap_all.php';

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

		if(isset($this->params['env']))
			return;
		if(file_exists($file = $this->params['root'].'/storage/environment'))
			$this->params['env'] = file_get_contents($file);
		elseif(defined('_ENV_'))
			$this->params['env'] = _ENV_;
		elseif($this->get('consoleMode')) {
			foreach($_SERVER['argv'] as $k=>$v) {
				if($v === '--env' && isset($_SERVER['argv'][$k+1])) {
					$this->params['env'] = $_SERVER['argv'][$k+1];
					return;
				}
			}
			$this->params['env'] = 'dev';
		}
		elseif(isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] == '127.0.0.1' || $_SERVER['HTTP_HOST'] == 'localhost'))
			$this->params['env'] = 'dev';
		else
			$this->params['env'] = 'prod';
	}

	/**
	 * Get the cache dependency.
	 * @return \Asgard\Cache\CacheInterface
	 */
	public function getCache() {
		if($this->cache) {
			$class = $this->getConfig()['cache'];
			$reflector = new \ReflectionClass($class);
			return $reflector->newInstanceArgs([$this->params['root'].'/storage/cache/']);
		}
		else
			return $this->cache;
	}

	/**
	 * Register the bundle's services.
	 * @return \Asgard\Container\ContainerInterface
	 */
	protected function buildContainer() {
		$cache = $this->getCache();
		if($cache) {
			if(($container = $cache->fetch('container')) instanceof \Asgard\Container\Container) {
				$container['kernel'] = $this;
				$this->container = $container;
				#make $this->container the default instance of Container, in case someones uses it
				\Asgard\Container\Container::setInstance($this->container);
				return $this->container;
			}
		}

		$bundles = $this->getAllBundles();
		#use the Container default instance, in case someones uses it
		$container = $this->container = \Asgard\Container\Container::singleton();
		$container['kernel'] = $this;

		foreach($bundles as $bundle)
			$bundle->buildContainer($container);

		if($cache)
			$cache->save('container', $container);

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
	 * @return \Asgard\Hook\AnnotationReader
	 */
	protected function getHooksAnnotationReader() {
		$AnnotationReader = new \Asgard\Hook\AnnotationReader();
		if($this->getCache())
			$AnnotationReader->setCache($this->getCache());
		$AnnotationReader->setDebug($this->getConfig()['debug']);
		return $AnnotationReader;
	}

	/**
	 * Get the controllers annotations reader dependency.
	 * @return \Asgard\Http\AnnotationReader
	 */
	protected function getControllersAnnotationReader() {
		$AnnotationReader = new \Asgard\Http\AnnotationReader();
		if($this->getCache())
			$AnnotationReader->setCache($this->getCache());
		$AnnotationReader->setDebug($this->getConfig()['debug']);
		return $AnnotationReader;
	}

	/**
	 * Actually fetch all the budles.
	 * @return array
	 */
	protected function doGetBundles() {
		$cache = $this->getCache();
		if($cache) {
			if(($res = $cache->fetch('bundles')) !== false)
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

		foreach($bundles as $bundle) {
			$bundle->setHooksAnnotationReader($this->getHooksAnnotationReader());
			$bundle->setControllersAnnotationReader($this->getControllersAnnotationReader());
		}

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
	 * Get a parameter.
	 * @param  string $name
	 * @return mixed
	 */
	public function get($name) {
		if(!isset($this->params[$name]))
			return;
		return $this->params[$name];
	}

	/**
	 * Set a parameter.
	 * @param  string $name
	 * @param  mixed  $value
	 * @return Kernel $this
	 */
	public function set($name, $value) {
		$this->params[$name] = $value;
		return $this;
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