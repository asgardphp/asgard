<?php
namespace Asgard\Email;

/**
 * Interface for email drivers.
 */
interface DriverInterface {
	/**
	 * Set the email transport options.
	 * @param mixed $transport
	 */
	public function transport($transport);

	/**
	 * Send an email.
	 * @param  callback $cb to forge the email
	 * @return boolean      true for success, false otherwise
	 */
	public function send($cb);
}