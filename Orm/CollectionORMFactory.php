<?php
namespace Asgard\Orm;

/**
 * CollectionORM factory.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class CollectionORMFactory implements CollectionORMFactoryInterface {
	/**
	 * Paginator factory.
	 * @var \Asgard\Common\PaginatorFactoryInterface
	 */
	protected $paginatorFactory;

	/**
	 * Constructor.
	 * @param \Asgard\Common\PaginatorFactoryInterface $paginatorFactory
	 */
	public function __construct(\Asgard\Common\PaginatorFactoryInterface $paginatorFactory=null) {
		$this->paginatorFactory = $paginatorFactory;
	}

	/**
	 * {@inheritDoc}
	 */
	public function create(\Asgard\Entity\Entity $entity, $name, DataMapperInterface $dataMapper, $locale=null, $prefix=null) {
		$relation = $dataMapper->relation($entity->getDefinition(), $name);
		if($relation->isPolymorphic())
			return new PolymorphicCollectionORM($entity, $name, $dataMapper, $locale, $prefix, $this->paginatorFactory);
		else
			return new CollectionORM($entity, $name, $dataMapper, $locale, $prefix, $this->paginatorFactory);
	}
}