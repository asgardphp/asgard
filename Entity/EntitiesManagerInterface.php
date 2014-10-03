<?php
namespace Asgard\Entity;

/**
 * Manage entities.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface EntitiesManagerInterface {
	/**
	 * Return the hooks manager.
	 * @return \Asgard\Hook\HooksManagerInterface
	 */
	public function getHooksManager();

	/**
	 * Set the hooks manager.
	 * @param \Asgard\Hook\HooksManagerInterface $hooksManager
	 */
	public function setHooksManager(\Asgard\Hook\HooksManagerInterface $hooksManager);

	/**
	 * Set the validator factory.
	 * @param \Asgard\Container\Factory $validatorFactory
	 */
	public function setValidatorFactory(\Asgard\Container\Factory $validatorFactory);

	/**
	 * Create a validator.
	 * @return \Asgard\Validation\ValidatorInterface
	 */
	public function createValidator();

	/**
	 * Set the cache dependency.
	 * @param \Asgard\Cache\CacheInterface $cache
	 */
	public function setCache(\Asgard\Cache\CacheInterface $cache);

	/**
	 * Get the cache dependency.
	 * @return \Asgard\Cache\CacheInterface
	 */
	public function getCache();

	/**
	 * Set default locale.
	 * @param string $defaultLocale
	 */
	public function setDefaultLocale($defaultLocale);

	/**
	 * Get default locale.
	 * @return string
	 */
	public function getDefaultLocale();

	/**
	 * Get an entity definition.
	 * @param  string $entityClass
	 * @return EntityDefinition
	 */
	public function get($entityClass);

	/**
	 * Check if has an entity definition.
	 * @param  string  $entityClass
	 * @return boolean
	 */
	public function has($entityClass);

	/**
	 * Return all definitions.
	 * @return array
	 */
	public function getDefinitions();

	/**
	 * Make a new entity.
	 * @param  string $entityClass
	 * @param  array  $attrs      entity attributes.
	 * @param  string $locale
	 * @return Entity
	 */
	public function make($entityClass, array $attrs=null, $locale=null);
}