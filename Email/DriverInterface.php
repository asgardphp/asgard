<?php
namespace Asgard\Email;

/**
 * Interface for email drivers.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface DriverInterface {
	/**
	 * Set the email transport options.
	 * @param mixed $transport
	 */
	public function transport($transport);

	/**
	 * Send an email.
	 * @param  callable $cb to forge the email
	 * @return boolean      true for success, false otherwise
	 */
	public function send($cb);
}