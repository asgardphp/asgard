<?php
namespace Asgard\Orm;

/**
 * ORM for related entities.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface CollectionORMInterface extends ORMInterface {
	/**
	 * Update the related entities.
	 * @param integer|array           $ids   array of entity ids
	 * @param boolean                 $force true to skip validation
	 * @return CollectionORMInterface        $this
	 */
	public function sync($ids, $force=false);

	/**
	 * Add new entities to the relation.
	 * @param integer|array $ids
	 */
	public function add($ids);

	/**
	 * Create a new entity and add it to the relation.
	 * @param  array $params entity default attributes
	 * @return \Asgard\Entity\Entitiy
	 */
	public function create(array $params=[]);

	/**
	 * Remove entities from the relation.
	 * @param  integer|array          $ids
	 * @return CollectionORMInterface $this
	 */
	public function remove($ids);
}
