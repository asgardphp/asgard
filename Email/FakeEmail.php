<?php
namespace Asgard\Email;

/**
 * A fake email sender. Write emails in a text file.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class FakeEmail implements DriverInterface {
	/**
	 * The destination file.
	 * @var transport
	 */
	protected $transport;

	/**
	 * {@inheritDoc}
	 */
	public function transport($transport) {
		$this->transport = $transport;
	}

	/**
	 * {@inheritDoc}
	 */
	public function send($cb) {
		$message = new SwiftMessage;

		$cb($message);

		$result = $message->toString();
		file_put_contents($this->transport['file'], $result);

		return true;
	}
}
