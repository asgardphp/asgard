<?php
namespace Asgard\Orm;

/**
 * ORM factory.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class ORMFactory implements ORMFactoryInterface {
	/**
	 * Constructor.
	 * @param \Asgard\Common\PaginatorFactoryInterface $paginatorFactory
	 */
	public function __construct(\Asgard\Common\PaginatorFactoryInterface $paginatorFactory=null) {
		$this->paginatorFactory = $paginatorFactory;
	}

	/**
	 * {@inheritDoc}
	 * @return ORM
	 */
	public function create($entityClass, DataMapperInterface $dataMapper, $locale=null, $prefix=null) {
		return new ORM($entityClass, $dataMapper, $locale=null, $prefix=null, $this->paginatorFactory);
	}
}