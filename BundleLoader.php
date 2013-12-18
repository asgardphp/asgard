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

namespace Coxis\Core {
	class BundleLoader {
		protected $bundle = null;

		public function load($queue) {
			$preload = \Coxis\Utils\Cache::get('bundles/'.$this->getBundle().'/preload', function() {
				$bundle = $this->getBundle();
				$preload = array();
				$preload = array_merge($preload, Autoloader::fetchPreloadDir($bundle.'/Entities'));
				$preload = array_merge($preload, Autoloader::fetchPreloadDir($bundle.'/libs'));
				$preload = array_merge($preload, Autoloader::fetchPreloadDir($bundle.'/controllers'));
				$preload = array_merge($preload, Autoloader::fetchPreloadDir($bundle.'/hooks'));
				if(php_sapi_name() === 'cli')
					$preload = array_merge($preload, Autoloader::fetchPreloadDir($bundle.'/cli'));
				return $preload;
			});
			Autoloader::addPreloadedClasses($preload);
		}

		public function run() {
			$this->loadLocales();
			$this->loadHooks();
			$this->loadControllers();
			if(php_sapi_name() === 'cli')
				$this->loadCLI();
		}

		protected function loadLocales() {
			$locales = \Coxis\Utils\Cache::get('bundles/'.$this->getBundle().'/locales', function() {
				return \App::get('locale')->fetchLocalesFromDir($this->getBundle().'/locales');
			});
			\App::get('locale')->addLocales($locales);
		}

		protected function loadHooks() {
			$hooks = \Coxis\Utils\Cache::get('bundles/'.$this->getBundle().'/hooks', function() {
				$hooks = array();
				if(file_exists($this->getBundle().'/hooks/')) {
					foreach(glob($this->getBundle().'/hooks/*.php') as $filename) {
						$class = \Coxis\Core\Importer::loadClassFile($filename);
						if(is_subclass_of($class, 'Coxis\Hook\HooksContainer'))
							$hooks = array_merge($hooks, $class::fetchHooks());
					}
				}
				return $hooks;
			});
			if(!is_array($hooks))
				return;
			\App::get('hook')->hooks($hooks);
		}

		protected function loadCLI() {
			$routes = \Coxis\Utils\Cache::get('bundles/'.$this->getBundle().'/cli', function() {
				$routes = array();
				if(file_exists($this->getBundle().'/cli/')) {
					foreach(glob($this->getBundle().'/cli/*.php') as $filename) {
						$class = \Coxis\Core\Importer::loadClassFile($filename);
						if(is_subclass_of($class, 'Coxis\Cli\CLIController'))
							$routes = array_merge($routes, $class::fetchRoutes());
					}
				}
				return $routes;
			});
			if(!is_array($routes))
				return;
			\Coxis\Cli\Facades\CLIRouter::addRoutes($routes);
		}

		protected function loadControllers() {
			$routes = \Coxis\Utils\Cache::get('bundles/'.$this->getBundle().'/controllers', function() {
				$routes = array();
				if(file_exists($this->getBundle().'/controllers/')) {
					foreach(glob($this->getBundle().'/controllers/*.php') as $k=>$filename) {
						$class = \Coxis\Core\Importer::loadClassFile($filename);
						if(is_subclass_of($class, 'Coxis\Core\Controller'))
							$routes = array_merge($routes, $class::fetchRoutes());
					}
				}
				return $routes;
			});
			if(!is_array($routes))
				return;
			\Coxis\Facades\Resolver::addRoutes($routes);
		}

		public function setBundle($bundle) {
			$this->bundle = $bundle;
		}

		public function getBundle() {
			if($this->bundle !== null)
				return $this->bundle;

			$reflector = new \ReflectionClass(get_called_class());
			return dirname($reflector->getFileName());
		}
	}
}