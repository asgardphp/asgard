<?php
namespace Asgard\Orm;

/**
 * Helps performing operations like selection, deletion and update on a set of entities.
 * @author Michel Hognerud <michel@hognerud.net>
*/
interface ORMInterface {
	/**
	 * Magic method.
	 *
	 * Lets you access relations of the entity.
	 * For example:
	 * $orm = new ORM('News');
	 * $orm->categories()->all();
	 *
	 * @param string $relationName The name of the relation.
	 * @param array  $args Not used.
	 *
	 * @throws \Exception If the relation does not exist.
	 *
	 * @return ORM A new ORMInterface instance of the related entities.
	*/
	public function __call($relationName, array $args);

	/**
	 * Magic Method.
	 *
	 * Lets you retrieve related entities.
	 * For example:
	 * $orm = new ORM('News');
	 * $categories = $orm->categories;
	 *
	 * @param string $name The name of the relation.
	 *
	 * @return array Array of entities.
	*/
	public function __get($name);

	/**
	 * Limits the search to the entities related to the given entity.
	 *
	 * @param string|EntityRelation $relation The name of the relation.
	 * @param \Asgard\Entity\Entity $entity The related entity.
	 *
	 * @return ORMInterface $this
	*/
	public function joinToEntity($relation, \Asgard\Entity\Entity $entity);

	/**
	 * Joins a relation to the search. Useful when having conditions involving relations.
	 *
	 * @param string|EntityRelation|array $relations The name of the relation or an array of relations.
	 *
	 * @return ORMInterface $this
	*/
	public function join($relations);

	/**
	 * Returns the name of the table.
	 *
	 * @return string
	*/
	public function getTable();

	/**
	 * Returns the name of the i18n table.
	 *
	 * @return string
	*/
	public function getTranslationTable();

	/**
	 * Returns the next entity in the search list.
	 *
	 * @return \Asgard\Entity\Entity
	*/
	public function next();

	/**
	 * Returns all the ids of the selected entities.
	 *
	 * @return array
	*/
	public function ids();

	/**
	 * Returns an array with all the values of a specific property from the selected entities.
	 *
	 * @param string $property
	 *
	 * @return array
	*/
	public function values($property);

	/**
	 * Returns the first entity from the search list.
	 *
	 * @return \Asgard\Entity\Entity
	*/
	public function first();

	/**
	 * Returns all the entities from the search list.
	 *
	 * @return array
	*/
	public function all();

	/**
	 * Returns the DAL object used to build queries.
	 *
	 * @return \Asgard\Db\DAL
	*/
	public function getDAL();

	/**
	 * Returns the array of entities from the search list.
	 *
	 * @throws \Exception If one the "with" relations does not exist.
	 *
	 * @return array Array of \Asgard\Entity\Entity
	*/
	public function get();

	/**
	 * Performs an SQL query and returns the entities.
	 *
	 * @param string $sql SQL query
	 * @param array  $args SQL parameters
	 *
	 * @return array Array of \Asgard\Entity\Entity
	*/
	public function selectQuery($sql, array $args=[]);

	/**
	 * Paginates the search list.
	 *
	 * @param integer $page Current page.
	 * @param integer $per_page Number of elements per page.
	 *
	 * @return \Asgard\Orm\ORMInterface $this
	*/
	public function paginate($page, $per_page=10);

	/**
	 * Returns the paginator tool.
	 *
	 * @return \Asgard\Common\PaginatorInterface
	*/
	public function getPaginator();

	/**
	 * Retrieves the related entities along with the selected entities.
	 *
	 * @param array|string $with Array of relation names or the name of the relation.
	 * @param \Closure     $closure Code to be executed on the relation's ORM.
	 *
	 * @return \Asgard\Orm\ORMInterface $this
	*/
	public function with($with, \Closure $closure=null);

	/**
	 * Add new conditions to the query.
	 *
	 * @param array|string $conditions Array of conditions or name of a property.
	 * @param string  $val Value of the property.
	 *
	 * @return \Asgard\Orm\ORMInterface $this
	*/
	public function where($conditions, $val=null);

	/**
	 * Sets the offset.
	 *
	 * @param integer $offset
	 *
	 * @return \Asgard\Orm\ORMInterface $this
	*/
	public function offset($offset);

	/**
	 * Sets the limit.
	 *
	 * @param integer $limit
	 *
	 * @return \Asgard\Orm\ORMInterface $this
	*/
	public function limit($limit);

	/**
	 * Sets the order.
	 *
	 * @param string $orderBy e.g. position ASC
	 *
	 * @return \Asgard\Orm\ORMInterface $this
	*/
	public function orderBy($orderBy);

	/**
	 * Deletes all the selected entities.
	 *
	 * @return integer The number of deleted entities.
	*/
	public function delete();

	/**
	 * Updates entities properties.
	 *
	 * @param array $values Array of properties.
	 *
	 * @return \Asgard\Orm\ORMInterface $this
	*/
	public function update(array $values);

	/**
	 * Counts the number of selected entities.
	 *
	 * @param string $group_by To split the result according to a specific property.
	 *
	 * @return string|array The total or an array of total per value.
	*/
	public function count($group_by=null);

	/**
	 * Returns the minimum value of a property.
	 *
	 * @param string $what The property to count from.
	 * @param string $group_by To split the result according to a specific property.
	 *
	 * @return string|array The total or an array of total per value.
	*/
	public function min($what, $group_by=null);

	/**
	 * Returns the maximum value of a property.
	 *
	 * @param string $what The property to count from.
	 * @param string $group_by To split the result according to a specific property.
	 *
	 * @return string|array The total or an array of total per value.
	*/
	public function max($what, $group_by=null);

	/**
	 * Returns the average value of a property.
	 *
	 * @param string $what The property to count from.
	 * @param string $group_by To split the result according to a specific property.
	 *
	 * @return string|array The total or an array of total per value.
	*/
	public function avg($what, $group_by=null);

	/**
	 * Returns the sum of a property.
	 *
	 * @param string $what The property to count from.
	 * @param string $group_by To split the result according to a specific property.
	 *
	 * @return string|array The total or an array of total per value.
	*/
	public function sum($what, $group_by=null);

	/**
	 * Resets all conditions, order, offset, and limit.
	 *
	 * @return \Asgard\Orm\ORMInterface $this
	*/
	public function reset();
}