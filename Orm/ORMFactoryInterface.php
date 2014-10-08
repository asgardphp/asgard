<?php
namespace Asgard\Orm;

/**
 * ORM factory interface.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface ORMFactoryInterface {
	/**
	 * Create a new instance.
	 * @param  string              $entityClass
	 * @param  DataMapperInterface $dataMapper
	 * @param  string              $locale
	 * @param  string              $prefix
	 * @return ORMInterface
	 */
	public function create($entityClass, DataMapperInterface $dataMapper, $locale=null, $prefix=null);
}