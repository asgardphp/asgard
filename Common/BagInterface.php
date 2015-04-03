<?php
namespace Asgard\Common;

/**
 * Bag to manipulate a set of data.
 * @author Michel Hognerud <michel@hognerud.com>
 * @api
 */
interface BagInterface extends \ArrayAccess {
	/**
	 * Return all data.
	 * @return array
	 * @api
	 */
	public function all();

	/**
	 * Clear data.
	 * @return BagInterface  $this
	 * @api
	 */
	public function clear();

	/**
	 * Return number of elements.
	 * @return integer
	 * @api
	 */
	public function count();

	/**
	 * Set all elements.
	 * @param array $data
	 * @return BagInterface  $this
	 * @api
	 */
	public function setAll($data);

	/**
	 * Set a value.
	 * @param string|array $path    nested keys separated by ".".
	 * @param mixed        $value
	 * @return BagInterface         $this
	 * @api
	 */
	public function set($path, $value=null);

	/**
	 * Get a value.
	 * @param string $path    nested keys separated by ".".
	 * @param mixed  $default
	 * @return mixed
	 * @api
	 */
	public function get($path, $default=null);

	/**
	 * Check if has element.
	 * @param  string  $path nested keys separated by ".".
	 * @return boolean
	 * @api
	 */
	public function has($path);

	/**
	 * Delete an element.
	 * @param string $path    nested keys separated by ".".
	 * @return BagInterface  $this
	 * @api
	 */
	public function delete($path);
}