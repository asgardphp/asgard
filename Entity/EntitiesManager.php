<?php
namespace Asgard\Entity;

/**
 * Manage entities.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class EntitiesManager implements EntitiesManagerInterface {
	use \Asgard\Container\ContainerAwareTrait;

	/**
	 * Default instance.
	 * @var EntitiesManagerInterface
	 */
	protected static $singleton;
	/**
	 * Entities definitions.
	 * @var array
	 */
	protected $definitions = [];
	/**
	 * Cache
	 * @var \Asgard\Cache\CacheInterface
	 */
	protected $cache;
	/**
	 * Default locale.
	 * @var string
	 */
	protected $defaultLocale = 'en';
	/**
	 * Validator factory.
	 * @var \Asgard\Validation\ValidatorFactoryInterface
	 */
	protected $validatorFactory;
	/**
	 * Hooks manager.
	 * @var \Asgard\Hook\HooksManagerInterface
	 */
	protected $hooksManager;

	/**
	 * Constructor.
	 * @param \Asgard\Container\ContainerInterface $container
	 */
	public function __construct(\Asgard\Container\ContainerInterface $container=null) {
		#need services container for entity behaviors only
		$this->setContainer($container);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHooksManager() {
		if(!$this->hooksManager)
			$this->hooksManager = new \Asgard\Hook\HooksManager;
		return $this->hooksManager;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setHooksManager(\Asgard\Hook\HooksManagerInterface $hooksManager) {
		$this->hooksManager = $hooksManager;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setValidatorFactory(\Asgard\Validation\ValidatorFactoryInterface $validatorFactory) {
		$this->validatorFactory = $validatorFactory;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function createValidator() {
		if(!$this->validatorFactory)
			return new \Asgard\Validation\Validator;
		else
			return $this->validatorFactory->create();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setCache(\Asgard\Cache\CacheInterface $cache) {
		$this->cache = $cache;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCache() {
		return $this->cache;
	}

	/**
	 * Return the default instance.
	 * @return EntitiesManagerInterface
	 */
	public static function singleton() {
		if(!static::$singleton)
			static::$singleton = new static(\Asgard\Container\Container::singleton());
		return static::$singleton;
	}

	/**
	 * Set default instance.
	 * @param EntitiesManagerInterface $instance
	 */
	public static function setInstance($instance) {
		static::$singleton = $instance;
		return $instance;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDefaultLocale($defaultLocale) {
		$this->defaultLocale = $defaultLocale;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefaultLocale() {
		return $this->defaultLocale;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get($entityClass) {
		if(!$this->has($entityClass))
			$this->makeDefinition($entityClass);

		return $this->definitions[$entityClass];
	}

	/**
	 * {@inheritDoc}
	 */
	public function has($entityClass) {
		return isset($this->definitions[$entityClass]);
	}

	/**
	 * Make a new entity definition.
	 * @param  string $entityClass
	 * @return Definition
	 */
	protected function makeDefinition($entityClass) {
		if($this->has($entityClass))
			return $this->definitions[$entityClass];

		$hooksManager = $this->getHooksManager();
		$definition = false;
		if($cache = $this->getCache())
			$definition = $cache->fetch('entitiesmanager.'.$entityClass.'.definition');
		if($definition === false)
			$definition = new Definition($entityClass, $this, $hooksManager);
		$definition->setEntitiesManager($this);
		$definition->setGeneralHooksManager($hooksManager);

		$this->definitions[$entityClass] = $definition;

		return $definition;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefinitions() {
		return $this->definitions;
	}

	/**
	 * {@inheritDoc}
	 */
	public function make($entityClass, array $attrs=null, $locale=null) {
		return $this->get($entityClass)->make($attrs, $locale);
	}
}