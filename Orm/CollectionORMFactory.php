<?php
namespace Asgard\Orm;

/**
 * CollectionORM factory.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class CollectionORMFactory implements CollectionORMFactoryInterface {
	/**
	 * Constructor.
	 * @param \Asgard\Common\PaginatorFactoryInterface $paginatorFactory
	 */
	public function __construct(\Asgard\Common\PaginatorFactoryInterface $paginatorFactory=null) {
		$this->paginatorFactory = $paginatorFactory;
	}

	/**
	 * {@inheritDoc}
	 * @return TemplateEngine
	 */
	public function create($entityClass, $name, DataMapperInterface $dataMapper, $locale=null, $prefix=null) {
		return new CollectionORM($entityClass, $name, $dataMapper, $lcoale, $prefix, $this->paginatorFactory);
	}
}