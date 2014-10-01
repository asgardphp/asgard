<?php
namespace Asgard\Common;

/**
 * Bag to manipulate a set of data.
 */
interface BagInterface extends \ArrayAccess {
	/**
	 * Return all data.
	 * @return array
	 */
	public function all();

	/**
	 * Clear data.
	 * @return BagInterface  $this
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
	 * @return BagInterface  $this
	 */
	public function setAll($data);
	
	/**
	 * Set a value.
	 * @param string|array $path    nested keys separated by ".".
	 * @param mixed        $value
	 * @return BagInterface         $this
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
	 * @return BagInterface  $this
	 */
	public function delete($path);
}