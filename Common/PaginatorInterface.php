<?php
namespace Asgard\Common;

/**
 * Paginator.
 * @author Michel Hognerud <michel@hognerud.com>
 * @api
 */
interface PaginatorInterface {
	/**
	 * Get start position.
	 * @return integer
	 * @api
	 */
	public function getStart();

	/**
	 * Get limit.
	 * @return integer
	 * @api
	 */
	public function getLimit();

	/**
	 * Get number of pages.
	 * @return integer
	 * @api
	 */
	public function getPages();

	/**
	 * Get first element position.
	 * @return integer
	 * @api
	 */
	public function getFirstNbr();

	/**
	 * Get last element position.
	 * @return integer
	 * @api
	 */
	public function getLastNbr();

	/**
	 * Render the pagination.
	 * @return string
	 * @api
	 */
	public function render();

	/**
	 * Check if has previous page.
	 * @return boolean
	 * @api
	 */
	public function hasPrev();

	/**
	 * Check if has next page.
	 * @return boolean
	 * @api
	 */
	public function hasNext();

	/**
	 * Get the previous page url.
	 * @return string
	 * @api
	 */
	public function getPrev();

	/**
	 * Get the next page url.
	 * @return string
	 * @api
	 */
	public function getNext();
}