<?php
namespace Asgard\Orm;

/**
 * ORM factory.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class ORMFactory implements ORMFactoryInterface {
	/**
	 * Pagnator factory.
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
	 * @return ORM
	 */
	public function create(\Asgard\Entity\Definition $definition, DataMapperInterface $dataMapper, $locale=null, $prefix=null) {
		return new ORM($definition, $dataMapper, $locale=null, $prefix=null, $this->paginatorFactory);
	}
}