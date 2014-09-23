<?php
namespace Asgard\Entity;

/**
 * Manage entities.
 */
class EntitiesManager {
	use \Asgard\Container\ContainerAwareTrait;

	/**
	 * Default instance.
	 * @var EntitiesManager
	 */
	protected static $singleton;
	/**
	 * Entities definitions.
	 * @var array
	 */
	protected $definitions = [];
	/**
	 * Cache
	 * @var \Asgard\Cache\Cache
	 */
	protected $cache;
	/**
	 * Default locale.
	 * @var string
	 */
	protected $defaultLocale;
	/**
	 * Validator factory.
	 * @var \Asgard\Container\Factory
	 */
	protected $validatorFactory;
	/**
	 * Hooks manager.
	 * @var \Asgard\Hook\HooksManager
	 */
	protected $hooksManager;

	/**
	 * Constructor.
	 * @param \Asgard\Container\Container $container
	 */
	public function __construct(\Asgard\Container\Container $container=null) {
		#need services container for entity behaviors only
		$this->setContainer($container);
	}

	/**
	 * Return the hooks manager.
	 * @return \Asgard\Hook\HooksManager
	 */
	public function getHooksManager() {
		if(!$this->hooksManager)
			$this->hooksManager = new \Asgard\Hook\HooksManager;
		return $this->hooksManager;
	}

	/**
	 * Set the hooks manager.
	 * @param \Asgard\Hook\HooksManager $hooksManager
	 */
	public function setHooksManager($hooksManager) {
		$this->hooksManager = $hooksManager;
		return $this;
	}

	/**
	 * Set the validator factory.
	 * @param \Asgard\Container\Factory $validatorFactory
	 */
	public function setValidatorFactory($validatorFactory) {
		$this->validatorFactory = $validatorFactory;
		return $this;
	}

	/**
	 * Create a validator.
	 * @return \Asgard\Validation\Validator
	 */
	public function createValidator() {
		if(!$this->validatorFactory)
			return new \Asgard\Validation\Validator;
		else
			return $this->validatorFactory->create();
	}

	/**
	 * Set the cache dependency.
	 * @param \Asgard\Cache\Cache $cache
	 */
	public function setCache($cache) {
		$this->cache = $cache;
		return $this;
	}

	/**
	 * Get the cache dependency.
	 * @return \Asgard\Cache\Cache
	 */
	public function getCache() {
		if(!$this->cache)
			$this->cache = new \Asgard\Cache\NullCache;
		return $this->cache;
	}

	/**
	 * Return the default instance.
	 * @return EntitiesManager
	 */
	public static function singleton() {
		if(!static::$singleton)
			static::$singleton = new static(\Asgard\Container\Container::singleton());
		return static::$singleton;
	}

	/**
	 * Set default instance.
	 * @param EntitiesManager $instance
	 */
	public static function setInstance($instance) {
		static::$singleton = $instance;
		return $instance;
	}

	/**
	 * Set default locale.
	 * @param string $defaultLocale
	 */
	public function setDefaultLocale($defaultLocale) {
		$this->defaultLocale = $defaultLocale;
		return $this;
	}

	/**
	 * Get default locale.
	 * @return string
	 */
	public function getDefaultLocale() {
		return $this->defaultLocale;
	}

	/**
	 * Get an entity definition.
	 * @param  string $entityClass
	 * @return EntityDefinition
	 */
	public function get($entityClass) {
		if(!$this->has($entityClass))
			$this->makeDefinition($entityClass);
		
		return $this->definitions[$entityClass];
	}

	/**
	 * Check if has an entity definition.
	 * @param  string  $entityClass
	 * @return boolean
	 */
	public function has($entityClass) {
		return isset($this->definitions[$entityClass]);
	}

	/**
	 * Make a new entity definition.
	 * @param  string $entityClass
	 * @return EntityDefinition
	 */
	protected function makeDefinition($entityClass) {
		if($this->has($entityClass))
			return $this->definitions[$entityClass];
		
		$hooksManager = $this->getHooksManager();
		$definition = $this->getCache()->fetch('entitiesmanager/'.$entityClass.'/definition', function() use($entityClass, $hooksManager) {
			$definition = new EntityDefinition($entityClass, $this, $hooksManager);
			return $definition;
		});
		$definition->setEntitiesManager($this);
		$definition->setGeneralHooksManager($hooksManager);

		$this->definitions[$entityClass] = $definition;

		return $definition;
	}

	/**
	 * Return all definitions.
	 * @return array
	 */
	public function getDefinitions() {
		return $this->definitions;
	}

	/**
	 * Make a new entity.
	 * @param  string $entityClass
	 * @param  array  $attrs      entity attributes.
	 * @param  string $locale
	 * @return Entity
	 */
	public function make($entityClass, array $attrs=null, $locale=null) {
		return $this->get($entityClass)->make($attrs, $locale);
	}
}