<?php
namespace Asgard\Orm;

/**
 * CollectionORM factory interface.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface CollectionORMFactoryInterface {
	/**
	 * Create a new instance.
	 * @param  \Asgard\Entity\Entity $entity
	 * @param  string                $name
	 * @param  DataMapperInterface   $dataMapper
	 * @param  string                $locale
	 * @param  string                $prefix
	 * @return CollectionORMInterface
	 */
	public function create($entityClass, $name, DataMapperInterface $dataMapper, $locale=null, $prefix=null);
}