<?php
namespace Asgard\Core;

class Kernel implements \ArrayAccess {
	use \Asgard\Container\ContainerAware;

	const VERSION = 0.1;
	protected $params = [];
	protected $config;
	protected $addedBundles = [];
	protected $bundles;
	protected $loaded = false;

	public function __construct($root=null) {
		$this['root'] = $root;
	}

	public function getContainer() {
		if(!$this->container) {
			$this->container = $this->buildContainer($this->getConfig()['cache']);
			$this->container['kernel'] = $this;
			\Asgard\Container\Container::setInstance($this->container);
		}
		return $this->container;
	}

	public function getConfig() {
		if(!$this->config) {
			$this->config = $config = new \Asgard\Config\Config();
			$config->loadConfigDir($this['root'].'/config', $this->getEnv());
		}
		return $this->config;
	}

	public function getEnv() {
		if(!isset($this->params['env']))
			$this->setDefaultEnvironment();
		return $this->params['env'];
	}

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

	protected function setDefaultEnvironment() {
		global $argv;

		if(isset($this['env']))
			return;
		if(defined('_ENV_'))
			$this['env'] = _ENV_;
		elseif($this['consoleMode']) {
			foreach($argv as $k=>$v) {
				if($v === '--env' && isset($argv[$k+1])) {
					$this['env'] = $argv[$k+1];
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

	protected function getCache($cache) {
		$reflector = new \ReflectionClass($cache);
		return $reflector->newInstanceArgs([$this['root'].'/storage/cache/']);
	}

	protected function buildContainer($cache=false) {
		if($cache) {
			$c = $this->getCache($cache);
			if(($this->container = $c->fetch('app')) !== false) {
				\Asgard\Container\Container::setInstance($this->container);
				return $this->container;
			}
		}

		$bundles = $this->getAllBundles();
		$container = $this->container = \Asgard\Container\Container::singleton();

		foreach($bundles as $bundle)
			$bundle->buildContainer($container);

		if($cache)
			$c->save('app', $container);

		return $container;
	}

	protected function runBundles() {
		$bundles = $this->getAllBundles();

		foreach($bundles as $bundle)
			$bundle->run($this->container);
	}

	public function getAllBundles() {
		if($this->bundles === null)
			$this->bundles = $this->doGetBundles();
		return $this->bundles;
	}

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

		return $bundles;
	}

	protected function getBundles() {
		return [];
	}

	public function addBundles($bundles) {
		$this->addedBundles = array_merge($this->addedBundles, $bundles);
	}

	public static function getVersion() {
		return static::VERSION;
	}

	public function offsetSet($offset, $value) {
		if(is_null($offset))
			throw new \LogicException('Offset must not be null.');
		else
			$this->params[$offset] = $value;
	}

	public function offsetExists($offset) {
		return isset($this->params[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->params[$offset]);
	}

	public function offsetGet($offset) {
		if(!isset($this->params[$offset]))
			return;
	return $this->params[$offset];
	}
}