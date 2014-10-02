<?php
namespace Asgard\Http;

/**
 * URL class.
 */
interface URLInterface {
	/**
	 * Return the url.
	 * @return string
	 */
	public function get();

	/**
	 * Set the url.
	 * @param string $url
	 */
	public function setURL($url);

	/**
	 * Set the host address.
	 * @param string $host
	 */
	public function setHost($host);

	/**
	 * Set the root path.
	 * @param string $root
	 */
	public function setRoot($root);

	/**
	 * Return the current url.
	 * @return string
	 */
	public function current();

	/**
	 * Return the url parameters.
	 * @param  array $params To override existing parameters.
	 * @return string
	 */
	public function getParams(array $params=[]);

	/**
	 * Return the full url.
	 * @param  arrray $params To override existing parameters.
	 * @return string
	 */
	public function full(array $params=[]);

	/**
	 * Return the base url.
	 * @return string
	 */
	public function base();

	/**
	 * Set the base url.
	 * @param string $base
	 */
	public function setBase($base);

	/**
	 * Create the absolute url to a relative one.
	 * @param  string $url relative url
	 * @return string
	 */
	public function to($url);

	/**
	 * Return the root path.
	 * @return string
	 */
	public function root();

	/**
	 * Return the host address.
	 * @return string
	 */
	public function host();

	/**
	 * Return the protocol.
	 * @return string
	 */
	public function protocol();

	/**
	 * Check if the url starts with a given string.
	 * @param  string $what
	 * @return boolean
	 */
	public function startsWith($what);
}
