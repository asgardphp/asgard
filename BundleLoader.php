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

		public function load(BundlesManager $queue) {
			if(\Asgard\Core\App::has('autoloader'))
				\Asgard\Core\App::get('autoloader')->preloadDir($this->getBundle());
		}

		public function run() {
			$bundleData = \Asgard\Core\App::get('cache')->get('bundles/'.$this->getID());
			if($bundleData !== null) {
				$locales = $bundleData['locales'];
				$hooks = $bundleData['hooks'];
				$cli = $bundleData['cli'];
				$routes = $bundleData['routes'];
			}
			else {
				$locales = $this->loadLocales();
				$hooks = $this->loadHooks();
				$cli = $this->loadCli();
				$routes = $this->loadControllers();
				
				\Asgard\Core\App::get('cache')->set('bundles/'.$this->getID(), array(
					'locales' => $locales,
					'hooks' => $hooks,
					'cli' => $cli,
					'routes' => $routes,
				));
			}

			if(\Asgard\Core\App::has('translator'))
				\Asgard\Core\App::get('translator')->addLocales($locales);

			if(\Asgard\Core\App::has('hook'))
				\Asgard\Core\App::get('hook')->hooks($hooks);

			if(\Asgard\Core\App::has('resolver'))
				\Asgard\Core\App::get('resolver')->addRoutes($routes);

			if(php_sapi_name() === 'cli' && \Asgard\Core\App::has('clirouter'))
				\Asgard\Core\App::get('clirouter')->addRoutes($cli);
		}

		protected function loadLocales() {
			if(!\Asgard\Core\App::has('translator'))
				return array();
			return \Asgard\Core\App::get('translator')->fetchLocalesFromDir($this->getBundle().'/locales');
		}

		protected function loadHooks() {
			if(!\Asgard\Core\App::has('hook'))
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

		protected function loadCLI() {
			if(!\Asgard\Core\App::has('clirouter'))
				return array();
			$routes = array();
			if(file_exists($this->getBundle().'/Cli/')) {
				foreach(glob($this->getBundle().'/Cli/*.php') as $filename) {
					$class = \Asgard\Core\Autoloader::loadClassFile($filename);
					if(is_subclass_of($class, 'Asgard\Core\Cli\CLIController'))
						$routes = array_merge($routes, $class::fetchRoutes());
				}
			}
			return $routes;
		}

		protected function loadControllers() {
			if(!\Asgard\Core\App::has('resolver'))
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