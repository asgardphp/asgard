<?php
namespace Asgard\Entity;

/**
 * 
 */
class EntitiesManager {
	use \Asgard\Container\ContainerAwareTrait;

	/**
	 * [$singleton description]
	 * @var [type]
	 */
	protected static $singleton;
	/**
	 * [$definitions description]
	 * @var [type]
	 */
	protected $definitions = [];
	/**
	 * [$cache description]
	 * @var [type]
	 */
	protected $cache;
	/**
	 * [$defaultLocale description]
	 * @var [type]
	 */
	protected $defaultLocale;
	/**
	 * [$validatorFactory description]
	 * @var [type]
	 */
	protected $validatorFactory;
	/**
	 * [$hooksManager description]
	 * @var [type]
	 */
	protected $hooksManager;

	/**
	 * [__construct description]
	 * @param \Asgard\Container\Container $container
	 */
	public function __construct(\Asgard\Container\Container $container=null) {
		#need services container for entity behaviors only
		$this->setContainer($container);
	}

	public function getHooksManager() {
		if(!$this->hooksManager)
			$this->hooksManager = new \Asgard\Hook\HooksManager;
		return $this->hooksManager;
	}

	public function setHooksManager($hooksManager) {
		$this->hooksManager = $hooksManager;
		return $this;
	}

	public function setValidatorFactory($validatorFactory) {
		$this->validatorFactory = $validatorFactory;
		return $this;
	}

	public function createValidator() {
		if(!$this->validatorFactory)
			return new \Asgard\Validation\Validator;
		else
			return $this->validatorFactory->create();
	}

	public function setCache($cache) {
		$this->cache = $cache;
		return $this;
	}

	public function getCache() {
		if(!$this->cache)
			$this->cache = new \Asgard\Cache\NullCache;
		return $this->cache;
	}

	public static function singleton() {
		if(!static::$singleton)
			static::$singleton = new static(\Asgard\Container\Container::singleton());
		return static::$singleton;
	}

	public static function setInstance($instance) {
		static::$singleton = $instance;
		return $instance;
	}

	public function setDefaultLocale($defaultLocale) {
		$this->defaultLocale = $defaultLocale;
		return $this;
	}

	public function getDefaultLocale() {
		return $this->defaultLocale;
	}

	public function get($entityClass) {
		if(!$this->has($entityClass))
			$this->makeDefinition($entityClass);
		
		return $this->definitions[$entityClass];
	}

	public function has($entityClass) {
		return isset($this->definitions[$entityClass]);
	}

	public function makeDefinition($entityClass) {
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

	public function getDefinitions() {
		return $this->definitions;
	}

	public function make($entityClass, array $params=null, $locale=null) {
		return $this->get($entityClass)->make($params, $locale);
	}
}