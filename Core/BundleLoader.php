<?php
namespace Asgard\Core;

/**
 * Bundles parent class.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class BundleLoader {
	use \Asgard\Container\ContainerAwareTrait;

	/**
	 * Bundle path.
	 * @var string
	 */
	protected $path;
	/**
	 * Hooks annotations reader.
	 * @var \Asgard\Hook\AnnotationsReader
	 */
	protected $hooksAnnotationsReader;
	/**
	 * Controllers annotations reader.
	 * @var \Asgard\Http\AnnotationsReader
	 */
	protected $controllersAnnotationsReader;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$reflector = new \ReflectionClass(get_called_class());
		$this->path = dirname($reflector->getFileName());
	}

	/**
	 * Set hooks annotations reader dependency.
	 * @param \Asgard\Hook\AnnotationsReader $hooksAnnotationsReader
	 */
	public function setHooksAnnotationsReader($hooksAnnotationsReader) {
		$this->hooksAnnotationsReader = $hooksAnnotationsReader;
		return $this;
	}

	/**
	 * Set controllers annotations reader dependency.
	 * @param \Asgard\Http\AnnotationsReader $controllersAnnotationsReader
	 */
	public function setControllersAnnotationsReader($controllersAnnotationsReader) {
		$this->controllersAnnotationsReader = $controllersAnnotationsReader;
		return $this;
	}

	/**
	 * Set bundle path.
	 * @param string $path
	 */
	public function setPath($path) {
		$this->path = realpath($path);
	}

	/**
	 * Get bundle path.
	 * @return sting
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Register services.
	 * @param \Asgard\Container\ContainerInterface $container
	 */
	public function buildContainer(\Asgard\Container\ContainerInterface $container) {
	}

	/**
	 * Run the bundle.
	 * @param  \Asgard\Container\ContainerInterface $container
	 */
	public function run(\Asgard\Container\ContainerInterface $container) {
		$bundleData = $container['cache']->fetch('bundles/'.$this->getID());
		if($bundleData !== false) {
			$hooks = $bundleData['hooks'];
			$routes = $bundleData['routes'];
		}
		else {
			$hooks = $container->has('hooks') ? $this->loadHooks():[];
			$routes = $container->has('resolver') ? $this->loadControllers():[];

			$container['cache']->save('bundles/'.$this->getID(), [
				'hooks' => $hooks,
				'routes' => $routes,
			]);
		}

		if($container->has('hooks'))
			$container['hooks']->hooks($hooks);

		if($container->has('resolver'))
			$container['resolver']->addRoutes($routes);

		if($container->has('translator'))
			$this->loadTranslations($container['translator']);

		if($container->has('console')) {
			$this->loadCommands($container['console']);
			$this->loadEntities($container['entitiesManager']);
		}
	}

	/**
	 * Load bundle's entities.
	 * @param  \Asgard\Entity\EntitiesManagerInterface $entitiesManager
	 */
	protected function loadEntities($entitiesManager) {
		foreach(glob($this->getPath().'/Entities/*.php') as $file) {
			$class = \Asgard\Common\Tools::loadClassFile($file);
			if(is_subclass_of($class, 'Asgard\Entity\Entity'))
				$entitiesManager->get($class);
		}
	}

	/**
	 * Load bundle's translations.
	 * @param  \Symfony\Component\Translation\Translator $translator
	 */
	protected function loadTranslations($translator) {
		foreach(glob($this->getPath().'/translations/'.$translator->getLocale().'/*') as $file)
			$translator->addResource('yaml', $file, $translator->getLocale());
	}

	/**
	 * Load bundle's commands.
	 * @param  \Asgard\Console\Application $console
	 */
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

	/**
	 * Return bundle's hooks.
	 * @return array
	 */
	protected function loadHooks() {
		$hooks = [];
		if(file_exists($this->getPath().'/Hooks/')) {
			foreach(glob($this->getPath().'/Hooks/*.php') as $filename) {
				$class = \Asgard\Common\Tools::loadClassFile($filename);
				if(is_subclass_of($class, 'Asgard\Hook\HooksContainer'))
					$hooks = array_merge_recursive($hooks, $this->hooksAnnotationsReader->fetchHooks($class));
			}
		}
		return $hooks;
	}

	/**
	 * Return bundle's controller routes.
	 * @return array
	 */
	protected function loadControllers() {
		$routes = [];
		if(file_exists($this->getPath().'/Controllers/')) {
			foreach(glob($this->getPath().'/Controllers/*.php') as $filename) {
				$class = \Asgard\Common\Tools::loadClassFile($filename);
				if(is_subclass_of($class, 'Asgard\Http\Controller'))
					$routes = array_merge($routes, $this->controllersAnnotationsReader->fetchRoutes($class));
			}
		}
		return $routes;
	}

	/**
	 * Get the bundle unique id.
	 * @return string
	 */
	protected function getID() {
		return sha1($this->getPath());
	}
}