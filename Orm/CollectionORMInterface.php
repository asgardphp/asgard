<?php
namespace Asgard\Orm;

/**
 * ORM for related entities.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface CollectionORMInterface extends ORMInterface {
	/**
	 * Update the related entities.
	 * @param array                   $ids    array of entity ids
	 * @param array|null              $groups validation groups
	 * @return CollectionORMInterface $this
	 */
	public function sync($ids, $groups=[]);

	/**
	 * Add new entities to the relation.
	 * @param  array $ids
	 * @return integer       number of added elements
	 */
	public function add($ids);

	/**
	 * Create a new entity and add it to the relation.
	 * @param  array      $params entity default attributes
	 * @param  array|null $groups validation groups
	 * @return \Asgard\Entity\Entity
	 */
	public function create(array $params=[], $groups=[]);

	/**
	 * Remove entities from the relation.
	 * @param  integer|array          $ids
	 * @return CollectionORMInterface $this
	 */
	public function remove($ids);

	/**
	 * Clear the relation entities.
	 * @return CollectionORMInterface $this
	 */
	public function clear();
}
