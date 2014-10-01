<?php
namespace Asgard\Common;

/**
 * Bag to manipulate a set of data.
 */
interface BagInterface {
	/**
	 * Return all data.
	 * @return array
	 */
	public function all();

	/**
	 * Clear data.
	 * @return Bag  $this
	 */
	public function clear();

	/**
	 * Return number of elements.
	 * @return integer
	 */
	public function size();

	/**
	 * Set all elements.
	 * @param array $data
	 * @return Bag  $this
	 */
	public function setAll($data);
	
	/**
	 * Set a value.
	 * @param string|array $path    nested keys separated by ".".
	 * @param mixed        $value
	 * @return Bag         $this
	 */
	public function set($path, $value=null);
	
	/**
	 * Get a value.
	 * @param string $path    nested keys separated by ".".
	 * @param mixed  $default
	 * @return mixed
	 */
	public function get($path, $default=null);
	
	/**
	 * Check if has element.
	 * @param string $path    nested keys separated by ".".
	 * @return boolean
	 */
	public function has($path);
	
	/**
	 * Delete an element.
	 * @param string $path    nested keys separated by ".".
	 * @return Bag  $this
	 */
	public function delete($path);
}