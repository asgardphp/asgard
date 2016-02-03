<?php
namespace Asgard\Http\Utils;

/**
 * Store messages in the container and display them on the next page.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface FlashInterface {
	/**
	 * Add a success message.
	 * @param string $message
	 */
	public function addSuccess($message);

	/**
	 * Add an error message.
	 * @param string $message
	 */
	public function addError($message);

	/**
	 * Add an info message.
	 * @param string $message
	 */
	public function addInfo($message);

	/**
	 * Add a warning message.
	 * @param string $message
	 */
	public function addWarning($message);

	/**
	 * Add a custom type message.
	 * @param string $type
	 * @param string $message
	 */
	public function add($type, $message);

	/**
	 * Show all messages.
	 * @param string  $cat
	 * @param boolean $cb  Use the global callback.
	 */
	public function showAll($cat=null, $cb=true);

	/**
	 * Show a custom type messages.
	 * @param  string $type
	 * @param  string $cat
	 * @param  callable $cb
	 */
	public function show($type, $cat=null, $cb=null);

	/**
	 * Set the display callback.
	 * @param  callable $cb
	 * @return Flash $this
	 */
	public function setCallback(callable $cb);

	/**
	 * Check if it contains a type.
	 * @param  string $type
	 * @return boolean
	 */
	public function has($type=null);

	/**
	 * Set a callback to handle display.
	 * @param  callable $globalCb
	 */
	public function setGlobalCallback(callable $globalCb);
}