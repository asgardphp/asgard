<?php
namespace Asgard\Orm;

/**
 * ORM factory interface.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface ORMFactoryInterface {
	/**
	 * Create a new instance.
	 * @param  \Asgard\Entity\EntityDefinition $definition
	 * @param  DataMapperInterface             $dataMapper
	 * @param  string                          $locale
	 * @param  string                          $prefix
	 * @return ORMInterface
	 */
	public function create(\Asgard\Entity\EntityDefinition $definition, DataMapperInterface $dataMapper, $locale=null, $prefix=null);
}