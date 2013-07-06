<?php
namespace {
	require_once('vendor/addendum/annotations.php');

	/* Controllers */
	class Annootate_Hook extends Annotation {}
	class Annootate_Prefix extends Annotation {}
	class Annootate_Priority extends Annotation {}
	class Annootate_Route extends Annotation {
		public $name;
		public $requirements;
		public $method;
	}
	class Annootate_Shortcut extends Annotation {}
	class Annootate_Usage extends Annotation {}
	class Annootate_Description extends Annotation {}
}

namespace Coxis\Core {
	abstract class BundlesManager {
		public static function loadBundle($bundle) {
			$bundle_load = array();
			if(file_exists($bundle.'/coxis_load.php'))
				$bundle_load = require $bundle.'/coxis_load.php';
			if(file_exists($bundle.'/../coxis_dirload.php'))
				$bundle_load = array_merge($bundle_load, require $bundle.'/../coxis_dirload.php');
			if(isset($bundle_load['type']))
				$bundle_type = $bundle_load['type'];
			else
				$bundle_type = null;

			if($bundle_type == 'mvc') {
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
		}
		
		public static function getBundles($directory = null) {
			if(\Config::get('phpCache') && $bundles=\Coxis\Utils\Cache::get('bundlesmanager/bundles'))
				return $bundles;
			else {
				$bundles = array();
				if(!$directory)
					$directories = \Config::get('bundle_directories');
				elseif(is_string($directory))
					$directories = array($directory);
				else
					$directories = $directory;
				foreach($directories as $dir)
					foreach(glob(_DIR_.$dir.'/*') as $bundlepath)
						$bundles[] = realpath($bundlepath);
				if(\Config::get('phpCache'))
					\Coxis\Utils\Cache::set('bundlesmanager/bundles', $bundles);
				return $bundles;
			}
		}
		
		public static function loadBundles($directory = null) {
			Profiler::checkpoint('loadBundles 1');
			$bundles = static::getBundles($directory);
			Profiler::checkpoint('loadBundles 2');

			if($bm=\Coxis\Utils\Cache::get('bundlesmanager')) {
				\CLIRouter::setRoutes($bm['cliroutes']);
				\Router::setRoutes($bm['routes']);
				\Coxis\Hook\HooksContainer::addHooks($bm['hooks']);
				\Locale::setLocales($bm['locales']);
				Autoloader::$preloaded = $bm['preloaded'];
			}
			else {
				foreach($bundles as $bundle)
					Autoloader::preloadDir($bundle.'/libs');
				foreach($bundles as $bundle)
					static::loadBundle($bundle);

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

			foreach($bundles as $bundle)
				if(file_exists($bundle.'/bundle.php'))
					include($bundle.'/bundle.php');
			Profiler::checkpoint('loadBundles 4');
		}

		public static function loadModelFixtures($bundle_path) {
			if(file_exists($bundle_path.'/data'))
				foreach(glob($bundle_path.'/data/*.models.yml') as $file)
					ORMManager::loadModelFixtures($file);
		}

		public static function loadModelFixturesAll() {
			foreach(static::getBundles() as $bundle)
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

				$reflection = new \ReflectionAnnotatedClass($classname);

				foreach(get_class_methods($classname) as $method) {
					if(!preg_match('/Action$/i', $method))
						continue;
					$method_reflection = new \ReflectionAnnotatedMethod($classname, $method);

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
