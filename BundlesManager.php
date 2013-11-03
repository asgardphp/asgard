<?php
namespace {
	/* Controllers */
	class Annootate_Hook extends Addendum\Annotation {}
	class Annootate_Prefix extends Addendum\Annotation {}
	class Annootate_Priority extends Addendum\Annotation {}
	class Annootate_Route extends Addendum\Annotation {
		public $name;
		public $requirements;
		public $method;
	}
	class Annootate_Shortcut extends Addendum\Annotation {}
	class Annootate_Usage extends Addendum\Annotation {}
	class Annootate_Description extends Addendum\Annotation {}
}

namespace Coxis\Core {
	class BundlesManager {
		protected static $inst = null;
		protected $bundles = array();

		/*public static function loadBundle($bundle) {
			$bundle_load = array();
			if(file_exists($bundle.'/coxis_load.php'))
				$bundle_load = require $bundle.'/coxis_load.php';
			if(file_exists($bundle.'/../coxis_dirload.php'))
				$bundle_load = array_merge(require $bundle.'/../coxis_dirload.php', $bundle_load);
			if(isset($bundle_load['type']))
				$bundle_type = $bundle_load['type'];
			else
				$bundle_type = null;

			if($bundle_type == 'mvc') { #todo replace mvc with "app"
				\Context::get('locale')->importLocales($bundle.'/locales');

				Autoloader::preloadDir($bundle.'/models');

				if(file_exists($bundle.'/hooks/')) {
					Autoloader::preloadDir($bundle.'/hooks');
					foreach(glob($bundle.'/hooks/*.php') as $filename)
						\Coxis\Core\Importer::loadClassFile($filename);
				}

				if(file_exists($bundle.'/controllers/')) {
					Autoloader::preloadDir($bundle.'/controllers');
					foreach(glob($bundle.'/controllers/*.php') as $filename)
						\Coxis\Core\Importer::loadClassFile($filename);
				}

				if(file_exists($bundle.'/cli/')) {
					Autoloader::preloadDir($bundle.'/cli');
					foreach(glob($bundle.'/cli/*.php') as $filename)
						\Coxis\Core\Importer::loadClassFile($filename);
				}
			}
		}*/
		
		public function getBundles($directory = null) {
			// return \Config::get('bundles');

			// if(\Config::get('phpCache') && $bundles=\Coxis\Utils\Cache::get('bundlesmanager/bundles'))
			// 	return $bundles;
			// else {
			// 	$bundles = array();
			// 	if(!$directory)
			// 		$directories = \Config::get('bundle_directories');
			// 	elseif(is_string($directory))
			// 		$directories = array($directory);
			// 	else
			// 		$directories = $directory;
			// 	foreach($directories as $dir)
			// 		foreach(glob(_DIR_.$dir.'/*') as $bundlepath)
			// 			$bundles[] = realpath($bundlepath);
			// 	if(\Config::get('phpCache'))
			// 		\Coxis\Utils\Cache::set('bundlesmanager/bundles', $bundles);
			// 	return $bundles;
			// }

			return $this->bundles;
		}
		
		public function getBundlesPath($directory = null) {
			$r = array();
			foreach($this->getBundles() as $bundle)
				$r[] = $bundle->getBundle();
			return $r;
		}

		public static function inst() {
			if(!static::$inst)
				static::$inst = new static;
			return static::$inst;
		}

		public function addBundle($bundle) {
			if(file_exists($bundle.'/Bundle.php') && ($b=include $bundle.'/Bundle.php') && is_subclass_of($b, 'Coxis\Core\BundleLoader'))
				$this->bundles[] = $b;
			else {
				$b = new \Coxis\Core\BundleLoader;
				$b->setBundle($bundle);
				$this->bundles[] = $b;
			}
		}
		
		function __construct() {
			Profiler::checkpoint('loadBundles 1');
			$bundles = \Config::get('bundles');
			Profiler::checkpoint('loadBundles 2');

			foreach($bundles as $bundle) {
				$this->addBundle($bundle);
			}

			foreach($this->bundles as $b) {
				if(is_subclass_of($b, 'Coxis\Core\BundleLoader'))
					$b->load($this);
			}
		}

		public static function loadBundles($directory = null) {
			$bundle_queue = static::inst();
			if($bm=\Coxis\Utils\Cache::get('bundlesmanager')) {
				\CLIRouter::setRoutes($bm['cliroutes']);
				\Router::setRoutes($bm['routes']);
				\Coxis\Hook\HooksContainer::addHooks($bm['hooks']);
				\Locale::setLocales($bm['locales']);
				Autoloader::$preloaded = $bm['preloaded'];
			}
			else {
				#todo run should always be called, even when there is no cache. Some code in the bundles run method should not though, like for preloading.
				foreach($bundle_queue->bundles as $b)
					$b->run();

				$cliroutes = static::getCLIRoutes();
				$routes = static::getRoutes();
				$hooks = static::getHooks();

				\CLIRouter::setRoutes($cliroutes);
				\Router::setRoutes($routes);
				
				foreach($hooks as $name=>$subhooks)
					foreach($subhooks as $hook)
						\Coxis\Hook\HooksContainer::addHook($name, $hook);

				\Coxis\Utils\Cache::set('bundlesmanager', array(
					'routes' => $routes,
					'cliroutes' => $cliroutes,
					'hooks' => $hooks,
					'locales' => \Context::get('locale')->getLocales(),
					'preloaded' => Autoloader::$preloaded,
				));
			}
			Profiler::checkpoint('loadBundles 3');
		}

		public static function loadModelFixtures($bundle_path) {
			if(file_exists($bundle_path.'/data'))
				foreach(glob($bundle_path.'/data/*.models.yml') as $file)
					ORMManager::loadModelFixtures($file);
		}

		public static function loadModelFixturesAll() {
			foreach(static::inst()->getBundlesPath() as $bundle)
				static::loadModelFixtures($bundle);
		}

		#todo better
		public static function getCLIRoutes($directory = false) {
			$routes = array();

			$controllers = get_declared_classes();
			$controllers = array_filter($controllers, function($controller) {
				return is_subclass_of($controller, 'Coxis\Core\CLI\CLIController');
			});
			foreach($controllers as $classname) {
				$r = new \ReflectionClass($classname);
				if(!$r->isInstantiable())
					continue;

				$reflection = new \Addendum\ReflectionAnnotatedClass($classname);

				foreach(get_class_methods($classname) as $method) {
					if(!preg_match('/Action$/i', $method))
						continue;
					$method_reflection = new \Addendum\ReflectionAnnotatedMethod($classname, $method);

					if($v = $method_reflection->getAnnotation('Shortcut')) {
						$usage = $description = '';
						if($u = $method_reflection->getAnnotation('Usage'))
							$usage = $u->value;
						if($d = $method_reflection->getAnnotation('Description'))
							$description = $d->value;
						$routes[] = array(
							'shortcut'	=>	$v->value,
							'controller'	=>	\Coxis\Core\Router::formatControllerName($classname),
							'action'	=>	\Coxis\Core\Router::formatActionName($method),
							'usage'		=>	$usage,
							'description'		=>	$description,
						);
					}
				}
			}

			return $routes;
		}

		public static function getRoutes($directory = false) {
			$routes = array();

			$controllers = get_declared_classes();
			$controllers = array_filter($controllers, function($controller) {
				return is_subclass_of($controller, 'Coxis\Core\Controller');
			});
			foreach($controllers as $classname) {
				$r = new \ReflectionClass($classname);
				if(!$r->isInstantiable())
					continue;
				if($directory)
					if(strpos($r->getFileName(), realpath($directory)) !== 0)
						continue;

				$routes = array_merge($routes, $classname::fetchRoutes());
			}

			return $routes;
		}

		public static function getHooks($directory = false) {
			$hooks = array();

			$controllers = get_declared_classes();
			$controllers = array_filter($controllers, function($controller) {
				return is_subclass_of($controller, 'Coxis\Hook\HooksContainer');
			});
			foreach($controllers as $classname) {
				$r = new \ReflectionClass($classname);
				if(!$r->isInstantiable())
					continue;
				if($directory)
					if(strpos($r->getFileName(), realpath($directory)) !== 0)
						continue;

				$hooks = array_merge_recursive($hooks, $classname::fetchHooks());
			}

			return $hooks;
		}

		public static function getRoutesFromDirectory($directory) {
			static::loadBundles($directory);
			list($routes) = static::getRoutesAndHooks($directory);
			return $routes;
		}
	}
}
