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
	 * @var \Asgard\Hook\AnnotationReader
	 */
	protected $hooksAnnotationReader;
	/**
	 * Controllers annotations reader.
	 * @var \Asgard\Http\AnnotationReader
	 */
	protected $controllersAnnotationReader;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$reflector = new \ReflectionClass(get_called_class());
		$this->path = dirname($reflector->getFileName());
	}

	/**
	 * Set hooks annotations reader dependency.
	 * @param \Asgard\Hook\AnnotationReader $hooksAnnotationReader
	 */
	public function setHooksAnnotationReader($hooksAnnotationReader) {
		$this->hooksAnnotationReader = $hooksAnnotationReader;
		return $this;
	}

	/**
	 * Set controllers annotations reader dependency.
	 * @param \Asgard\Http\AnnotationReader $controllersAnnotationReader
	 */
	public function setControllersAnnotationReader($controllersAnnotationReader) {
		$this->controllersAnnotationReader = $controllersAnnotationReader;
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
		$bundleData = $container['systemcache']->fetch('asgard.bundles.'.$this->getID());
		if($bundleData !== false) {
			$hooks = $bundleData['hooks'];
			$routes = $bundleData['routes'];
		}
		else {
			$hooks = $container->has('hooks') ? $this->loadHooks():[];
			$routes = $container->has('resolver') ? $this->loadControllers():[];

			$container['systemcache']->save('asgard.bundles.'.$this->getID(), [
				'hooks' => $hooks,
				'routes' => $routes,
			]);
		}

		if($container->has('hooks'))
			$container['hooks']->hooks($hooks);

		if($container->has('resolver'))
			$container['resolver']->addRoutes($routes);

		if($container->has('translation'))
			$this->loadTranslations($container['translation']);

		if($container->has('console')) {
			$this->loadCommands($container['console']);
			$this->loadEntities($container['entityManager']);
		}
	}

	/**
	 * Load bundle's entities.
	 * @param  \Asgard\Entity\EntityManagerInterface $entityManager
	 */
	protected function loadEntities($entityManager) {
		foreach(glob($this->getPath().'/Entities/*.php') as $file) {
			$class = \Asgard\Common\Tools::loadClassFile($file);
			if(is_subclass_of($class, 'Asgard\Entity\Entity'))
				$entityManager->get($class);
		}
	}

	/**
	 * Load bundle's translations.
	 * @param  \Asgard\Translation\Translation $translation
	 */
	protected function loadTranslations(\Asgard\Translation\Translation $translation) {
		$translation->addDir($this->getPath().'/translations');
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
				if(is_subclass_of($class, 'Asgard\Hook\HookContainer'))
					$hooks = array_merge_recursive($hooks, $this->hooksAnnotationReader->fetchHooks($class));
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
					$routes = array_merge($routes, $this->controllersAnnotationReader->fetchRoutes($class));
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