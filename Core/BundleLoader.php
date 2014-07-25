<?php
namespace Asgard\Core;

class BundleLoader {
	protected $path;
	protected $app;

	public function __construct() {
		$reflector = new \ReflectionClass(get_called_class());
		$this->path = dirname($reflector->getFileName());
	}

	public function setPath($path) {
		$this->path = realpath($path);
	}

	public function getPath() {
		return $this->path;
	}

	public function buildApp($app) {
	}

	public function run($app) {
		$bundleData = $app['cache']->fetch('bundles/'.$this->getID());
		if($bundleData !== false) {
			$hooks = $bundleData['hooks'];
			$routes = $bundleData['routes'];
		}
		else {
			$hooks = $app->has('hooks') ? $this->loadHooks():[];
			$routes = $app->has('resolver') ? $this->loadControllers():[];
			
			$app['cache']->save('bundles/'.$this->getID(), [
				'hooks' => $hooks,
				'routes' => $routes,
			]);
		}

		if($app->has('hooks'))
			$app['hooks']->hooks($hooks);

		if($app->has('resolver'))
			$app['resolver']->addRoutes($routes);

		if($app->has('translator'))
			$this->loadTranslations($app['translator']);

		if($app->has('console')) {
			$this->loadCommands($app['console']);
			$this->loadEntities($app['entitiesManager']);
		}
	}

	protected function loadEntities($entitiesManager) {
		foreach(glob($this->getPath().'/Entities/*.php') as $file) {
			$class = \Asgard\Common\Tools::loadClassFile($file);
			if(is_subclass_of($class, 'Asgard\Entity\Entity'))
				$entitiesManager->addEntity($class);
		}
	}

	protected function loadTranslations($translator) {
		foreach(glob($this->getPath().'/translations/'.$translator->getLocale().'/*') as $file)
			$translator->addResource('yaml', $file, $translator->getLocale());
	}

	protected function loadCommands($console) {
		if(file_exists($this->getPath().'/Commands/')) {
			foreach(glob($this->getPath().'/Commands/*.php') as $filename) {
				$class = \Asgard\Common\Tools::loadClassFile($filename);
				if(is_subclass_of($class, 'Symfony\Component\Console\Command\Command')) {
					try {
						$console->add(new $class);
					} catch(\Exception $e) {} #ignore if it cannot be instantiated without arguments
				}
			}
		}
	}

	protected function loadHooks() {
		$hooks = [];
		if(file_exists($this->getPath().'/Hooks/')) {
			foreach(glob($this->getPath().'/Hooks/*.php') as $filename) {
				$class = \Asgard\Common\Tools::loadClassFile($filename);
				if(is_subclass_of($class, 'Asgard\Hook\HooksContainer'))
					$hooks = array_merge_recursive($hooks, $class::fetchHooks());
			}
		}
		return $hooks;
	}

	protected function loadControllers() {
		$routes = [];
		if(file_exists($this->getPath().'/Controllers/')) {
			foreach(glob($this->getPath().'/Controllers/*.php') as $filename) {
				$class = \Asgard\Common\Tools::loadClassFile($filename);
				if(is_subclass_of($class, 'Asgard\Http\Controller'))
					$routes = array_merge($routes, $class::fetchRoutes());
			}
		}
		return $routes;
	}

	protected function getID() {
		return sha1($this->getPath());
	}
}