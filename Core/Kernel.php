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
	 * Shutdown callbacks.
	 * @var array
	 */
	protected $onShutdown;
	/**
	 * Compiled classes file path.
	 * @var string
	 */
	protected $compiledFile;
	/**
	 * Error handler.
	 * @var \Asgard\Debug\ErrorHandler
	 */
	protected $errorHandler;

	/**
	 * Constructor.
	 * @param string $root
	 * @param string $env
	 */
	public function __construct($root=null, $env=null) {
		$this->setRoot($root);
		$this->setEnv($env);
	}

	public function setCache(\Asgard\Cache\CacheInterface $cache) {
		$this->cache = $cache;
	}

	/**
	 * Get the services container.
	 * @return \Asgard\Container\ContainerInterface
	 */
	public function getContainer() {
		if(!$this->container) {
			$this->container = $this->buildContainer();
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
			$this->config = $config = new \Asgard\Config\Config($this->getCache());
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
	 * Set the environment.
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

		$this->setup();
		$this->loadBundles();
	}

	/**
	 * Setup errorhandler, shutdown and load compiled classes.
	 */
	public function setup() {
		$this->errorHandler = $errorHandler = \Asgard\Debug\ErrorHandler::register();
		if(php_sapi_name() !== 'cli')
			\Asgard\Debug\Debug::setFormat('html');

		register_shutdown_function([$this, 'shutdownFunction']);
		$this->addShutdownCallback([$errorHandler, 'shutdownFunction']);

		$compiledFile = $this->getCompiledFile();
		if($compiledFile && file_exists($compiledFile))
			include_once $compiledFile;

		return $this;
	}

	/**
	 * Load the bundles.
	 * @return Kernel $this
	 */
	public function loadBundles() {
		if($this->loaded)
			return;

		$this->bundles = $this->doGetBundles();
		$container = $this->getContainer();

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
	 * Called on shutdown.
	 */
	public function shutdownFunction() {
		foreach($this->onShutdown as $cb)
			call_user_func($cb);
	}

	/**
	 * Add a callback on shutdown.
	 * @param callable $cb
	 */
	public function addShutdownCallback($cb) {
		$this->onShutdown[] = $cb;
	}

	/**
	 * Set the default environment.
	 */
	protected function setDefaultEnvironment() {
		#Using _ENV_ and $_SERVER only as the last chance to guess the environment.
		#User can and should set the environment through constructor or setEnv($env).

		if(isset($this->params['env']))
			return;
		if(defined('_ENV_'))
			$this->params['env'] = _ENV_;
		elseif(file_exists($file = $this->params['root'].'/storage/environment'))
			$this->params['env'] = trim(file_get_contents($file));
		elseif($this->get('consoleMode')) {
			foreach($_SERVER['argv'] as $k=>$v) {
				if($v === '--env' && isset($_SERVER['argv'][$k+1])) {
					$this->params['env'] = $_SERVER['argv'][$k+1];
					return;
				}
			}
			$this->params['env'] = 'dev';
		}
		elseif(php_sapi_name() === 'cli' || (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] == '127.0.0.1' || $_SERVER['HTTP_HOST'] == 'localhost')))
			$this->params['env'] = 'dev';
		else
			$this->params['env'] = 'prod';
	}

	/**
	 * Get the cache dependency.
	 * @return \Asgard\Cache\CacheInterface
	 */
	public function getCache() {
		return $this->cache;
	}

	/**
	 * Register the bundle's services.
	 * @return \Asgard\Container\ContainerInterface
	 */
	protected function buildContainer() {
		$cache = $this->getCache();
		if($cache) {
			if(($container = $cache->fetch('asgard.container')) instanceof \Asgard\Container\Container) {
				$container['kernel'] = $this;
				$container['errorHandler'] = $this->errorHandler;

				$this->container = $container;
				#make $this->container the default instance of Container, in case someones uses it
				\Asgard\Container\Container::setInstance($this->container);
				return $this->container;
			}
		}

		$bundles = $this->getAllBundles();
		#use the Container default instance, in case someones uses it
		$container = $this->container = \Asgard\Container\Container::singleton();
		$container['errorHandler'] = $this->errorHandler;
		$container['kernel'] = $this;
		$container['config'] = $this->getConfig();

		foreach($bundles as $bundle)
			$bundle->buildContainer($container);

		if($cache)
			$cache->save('asgard.container', $container);

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
	public function getHooksAnnotationReader() {
		$AnnotationReader = new \Asgard\Hook\AnnotationReader;
		if($this->getCache())
			$AnnotationReader->setCache($this->getCache());
		$AnnotationReader->setDebug($this->getConfig()['debug']);
		return $AnnotationReader;
	}

	/**
	 * Get the controllers annotations reader dependency.
	 * @return \Asgard\Http\AnnotationReader
	 */
	public function getControllersAnnotationReader() {
		$AnnotationReader = new \Asgard\Http\AnnotationReader;
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
		if($cache)
			$bundles = $cache->fetch('asgard.bundles');

		if(!isset($bundles) || $bundles === false) {
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
			$kbundles = array_keys($bundles);
			$vbundles = array_values($bundles);
			foreach($vbundles as $k=>$b) {
				$key = $kbundles[$k];
				for($i=$k+1; isset($bundles[$i]); $i++) {
					if($b->getPath() === $bundles[$i]->getPath())
						unset($bundles[$k]);
				}
			}

			foreach($bundles as $bundle) {
				$bundle->setHooksAnnotationReader($this->getHooksAnnotationReader());
				$bundle->setControllersAnnotationReader($this->getControllersAnnotationReader());
			}

			if($cache)
				$cache->save('asgard.bundles', $bundles);
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

	/**
	 * Set the compiled classes file.
	 * @return Kernel $this
	 */
	public function setCompiledFile($compiledFile) {
		$this->compiledFile = $compiledFile;
		return $this;
	}

	/**
	 * Get the compiled classes file.
	 * @return string
	 */
	public function getCompiledFile() {
		if($this->compiledFile === null)
			$this->compiledFile = $this->params['root'].'/storage/compiled.php';#default path
		return $this->compiledFile;
	}
}