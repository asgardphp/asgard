<?php
namespace {
	class Annotate_Hook extends Addendum\Annotation {}
	class Annotate_Priority extends Addendum\Annotation {}
	class Annotate_Prefix extends Addendum\Annotation {}
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
			if(\Asgard\Core\App::get('autoloader')) {
				\Asgard\Core\App::get('autoloader')->preloadDir(_ASGARD_DIR_.'/entities');
				\Asgard\Core\App::get('autoloader')->preloadDir(_ASGARD_DIR_.'/libs');
				\Asgard\Core\App::get('autoloader')->preloadDir(_ASGARD_DIR_.'/controllers');
				\Asgard\Core\App::get('autoloader')->preloadDir(_ASGARD_DIR_.'/hooks');
				if(php_sapi_name() === 'cli')
					\Asgard\Core\App::get('autoloader')->preloadDir(_ASGARD_DIR_.'/cli');
			}
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
				return \Asgard\Core\App::get('locale')->fetchLocalesFromDir($this->getBundle().'/locales');
			});
			\Asgard\Core\App::get('locale')->addLocales($locales);
		}

		protected function loadHooks() {
			$hooks = \Asgard\Core\App::get('cache')->get('bundles/'.$this->getBundle().'/hooks', function() {
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
			$routes = \Asgard\Core\App::get('cache')->get('bundles/'.$this->getBundle().'/cli', function() {
				$routes = array();
				if(file_exists($this->getBundle().'/cli/')) {
					foreach(glob($this->getBundle().'/cli/*.php') as $filename) {
						$class = \Asgard\Core\Autoloader::loadClassFile($filename);
						if(is_subclass_of($class, 'Asgard\Cli\CLIController'))
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
			$routes = \Asgard\Core\App::get('cache')->get('bundles/'.$this->getBundle().'/controllers', function() {
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

		public function getBundle() {
			if($this->bundle == null) {
				$reflector = new \ReflectionClass(get_called_class());
				$this->bundle = dirname($reflector->getFileName());
			}
			return $this->bundle;
		}
	}
}