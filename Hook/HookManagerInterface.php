<?php
namespace Asgard\Hook;

/**
 * Hooks Manager.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface HookManagerInterface {
	/**
	 * Trigger a hook.
	 * @param string    $name
	 * @param array     $args
	 * @param callable  $cb Default callback.
	 * @param Chain $chain
	 * @param HooksChain
	*/
	public function trigger($name, array $args=[], $cb=null, &$chain=null);

	/**
	 * Check if a hook is present.
	 * @param string   $identifier
	 * @return boolean
	*/
	public function has($identifier);

	/**
	 * Return hooks.
	 * @param string $identifier Hook identifier.
	 * @return array Callbacks.
	*/
	public function get($identifier);

	/**
	 * Set a hook.
	 * @param string   $identifier
	 * @param callable $cb
	*/
	public function hook($identifier, $cb);

	/**
	 * Set a "before" hook.
	 * @param string   $identifier
	 * @param callable $cb
	*/
	public function hookBefore($identifier, $cb);

	/**
	 * Set an "after" hook.
	 * @param string   $identifier
	 * @param callable $cb
	*/
	public function hookAfter($identifier, $cb);

	/**
	 * Set multiple hooks.
	 * @param array $hooks
	*/
	public function hooks(array $hooks);
}