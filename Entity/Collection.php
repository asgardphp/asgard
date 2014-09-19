<?php
namespace Asgard\Entity;

/**
 * Entities collection.
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