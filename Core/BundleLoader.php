<?php
namespace {
	class Annotate_Hook extends Addendum\Annotation {}
	class Annotate_Priority extends Addendum\Annotation {}
	class Annotate_Prefix extends Addendum\Annotation {}
	class Annotate_Test extends Addendum\Annotation {}
	class Annotate_Route extends Addendum\Annotation {
		public $name;
		public $requirements;
		public $method;
		public $host;
	}
	class Annotate_Shortcut extends Addendum\Annotation {}
	class Annotate_Usage extends Addendum\Annotation {}
	class Annotate_Description extends Addendum\Annotation {}
}

namespace Asgard\Core {
	class BundleLoader {
		protected $bundle;
		protected $app;

		public function setApp($app) {
			$this->app = $app;
		}

		public function load(BundlesManager $bundlesManager) {
			if($this->app->has('autoloader'))
				$this->app['autoloader']->preloadDir($this->getBundle());
		}

		public function run() {
			$bundleData = $this->app['cache']->get('bundles/'.$this->getID());
			if($bundleData !== null) {
				$locales = $bundleData['locales'];
				$hooks = $bundleData['hooks'];
				$consoleRoutes = $bundleData['consoleRoutes'];
				$routes = $bundleData['routes'];
			}
			else {
				$locales = $this->loadLocales();
				$hooks = $this->loadHooks();
				$consoleRoutes = $this->loadConsole();
				$routes = $this->loadControllers();
				
				$this->app['cache']->set('bundles/'.$this->getID(), array(
					'locales' => $locales,
					'hooks' => $hooks,
					'consoleRoutes' => $consoleRoutes,
					'routes' => $routes,
				));
			}

			if($this->app->has('translator'))
				$this->app['translator']->addLocales($locales);

			if($this->app->has('hook'))
				$this->app['hook']->hooks($hooks);

			if($this->app->has('resolver'))
				$this->app['resolver']->addRoutes($routes);

			if(php_sapi_name() === 'cli' && $this->app->has('clirouter'))
				$this->app['clirouter']->addRoutes($consoleRoutes);
		}

		protected function loadLocales() {
			if(!$this->app->has('translator'))
				return array();
			return $this->app['translator']->fetchLocalesFromDir($this->getBundle().'/locales');
		}

		protected function loadHooks() {
			if(!$this->app->has('hook'))
				return array();
			$hooks = array();
			if(file_exists($this->getBundle().'/hooks/')) {
				foreach(glob($this->getBundle().'/hooks/*.php') as $filename) {
					$class = \Asgard\Core\Autoloader::loadClassFile($filename);
					if(is_subclass_of($class, 'Asgard\Hook\HooksContainer'))
						$hooks = array_merge_recursive($hooks, $class::fetchHooks());
				}
			}
			return $hooks;
		}

		protected function loadConsole() {
			if(!$this->app->has('clirouter'))
				return array();
			$routes = array();
			if(file_exists($this->getBundle().'/Console/')) {
				foreach(glob($this->getBundle().'/Console/*.php') as $filename) {
					$class = \Asgard\Core\Autoloader::loadClassFile($filename);
					if(is_subclass_of($class, 'Asgard\Console\Controller'))
						$routes = array_merge($routes, $class::fetchRoutes());
				}
			}
			return $routes;
		}

		protected function loadControllers() {
			if(!$this->app->has('resolver'))
				return array();
			$routes = array();
			if(file_exists($this->getBundle().'/controllers/')) {
				foreach(glob($this->getBundle().'/controllers/*.php') as $k=>$filename) {
					$class = \Asgard\Core\Autoloader::loadClassFile($filename);
					if(is_subclass_of($class, 'Asgard\Http\Controller'))
						$routes = array_merge($routes, $class::fetchRoutes());
				}
			}
			return $routes;
		}

		public function setBundle($bundle) {
			$this->bundle = realpath($bundle);
		}

		public function getID() {
			return sha1($this->getBundle());
		}

		public function getBundle() {
			if($this->bundle == null) {
				$reflector = new \ReflectionClass(get_called_class());
				$this->bundle = dirname($reflector->getFileName());
			}
			return realpath($this->bundle);
		}
	}
}