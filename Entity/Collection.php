<?php
namespace Asgard\Entity;

/**
 * Entities collection.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface Collection {
	/**
	 * Synchronize entities.
	 * @param  array $ids
	 */
	public function sync($ids);

	/**
	 * Add entities.
	 * @param array $ids
	 */
	public function add($ids);

	/**
	 * Remove entities.
	 * @param  array $ids
	 */
	public function remove($ids);
}