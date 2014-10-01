<?php
namespace Asgard\Common;

/**
 * Paginator.
 */
interface PaginatorInterface {
	/**
	 * Get start position.
	 * @return integer
	 */
	public function getStart();
	
	/**
	 * Get limit.
	 * @return integer
	 */
	public function getLimit();
	
	/**
	 * Get number of pages.
	 * @return integer
	 */
	public function getPages();
	
	/**
	 * Get first element position.
	 * @return integer
	 */
	public function getFirstNbr();
	
	/**
	 * Get last element position.
	 * @return integer
	 */
	public function getLastNbr();
	
	/**
	 * Render the pagination.
	 * @return string
	 */
	public function render();
	
	/**
	 * Check if has previous page.
	 * @return boolean
	 */
	public function hasPrev();
	
	/**
	 * Check if has next page.
	 * @return boolean
	 */
	public function hasNext();
	
	/**
	 * Get the previous page url.
	 * @return string
	 */
	public function getPrev();
	
	/**
	 * Get the next page url.
	 * @return string
	 */
	public function getNext();
}