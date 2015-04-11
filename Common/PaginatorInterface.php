<?php
namespace Asgard\Common;

/**
 * Paginator.
 * @author Michel Hognerud <michel@hognerud.com>
 * @api
 */
interface PaginatorInterface {
	/**
	 * Get limit.
	 * @return integer
	 * @api
	 */
	public function getPerPage();

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
	public function getFirst();

	/**
	 * Get last element position.
	 * @return integer
	 * @api
	 */
	public function getLast();

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