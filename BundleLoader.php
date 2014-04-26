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
		protected $bundle = null;

		public function load($queue) {
			if(\Asgard\Core\App::has('autoloader'))
				\Asgard\Core\App::get('autoloader')->preloadDir($this->getBundle());
		}

		public function run() {
			$this->loadLocales();
			$this->loadHooks();
			$this->loadControllers();
			if(php_sapi_name() === 'cli')
				$this->loadCLI();
		}

		protected function loadLocales() {
			$locales = \Asgard\Core\App::get('cache')->get('bundles/'.$this->getBundle().'/locales', function() {
				return \Asgard\Core\App::get('translator')->fetchLocalesFromDir($this->getBundle().'/locales');
			});
			\Asgard\Core\App::get('translator')->addLocales($locales);
		}

		protected function loadHooks() {
			$hooks = \Asgard\Core\App::get('cache')->get('bundles/'.$this->getID().'/hooks', function() {
				$hooks = array();
				if(file_exists($this->getBundle().'/hooks/')) {
					foreach(glob($this->getBundle().'/hooks/*.php') as $filename) {
						$class = \Asgard\Core\Autoloader::loadClassFile($filename);
						if(is_subclass_of($class, 'Asgard\Hook\HooksContainer'))
							$hooks = array_merge($hooks, $class::fetchHooks());
					}
				}
				return $hooks;
			});
			if(!is_array($hooks))
				return;
			\Asgard\Core\App::get('hook')->hooks($hooks);
		}

		protected function loadCLI() {
			$routes = \Asgard\Core\App::get('cache')->get('bundles/'.$this->getID().'/cli', function() {
				$routes = array();
				if(file_exists($this->getBundle().'/Cli/')) {
					foreach(glob($this->getBundle().'/Cli/*.php') as $filename) {
						$class = \Asgard\Core\Autoloader::loadClassFile($filename);
						if(is_subclass_of($class, 'Asgard\Core\Cli\CLIController'))
							$routes = array_merge($routes, $class::fetchRoutes());
					}
				}
				return $routes;
			});
			if(!is_array($routes))
				return;
			\Asgard\Core\App::get('clirouter')->addRoutes($routes);
		}

		protected function loadControllers() {
			$routes = \Asgard\Core\App::get('cache')->get('bundles/'.$this->getID().'/controllers', function() {
				$routes = array();
				if(file_exists($this->getBundle().'/controllers/')) {
					foreach(glob($this->getBundle().'/controllers/*.php') as $k=>$filename) {
						$class = \Asgard\Core\Autoloader::loadClassFile($filename);
						if(is_subclass_of($class, 'Asgard\Core\Controller'))
							$routes = array_merge($routes, $class::fetchRoutes());
					}
				}
				return $routes;
			});
			if(!is_array($routes))
				return;
			\Asgard\Core\App::get('resolver')->addRoutes($routes);
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