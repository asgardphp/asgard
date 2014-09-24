<?php
namespace Asgard\Email;

/**
 * A fake email sender. Write emails in a text file.
 */
class FakeEmail implements DriverInterface {
	/**
	 * The destination file.
	 * @var string
	 */
	protected $file;

	/**
	 * {@inheritDoc}
	 */
	public function transport($file) {
		$this->file = $file;
	}

	/**
	 * {@inheritDoc}
	 */
	public function send($cb) {
		$message = new SwiftMessage;

		$cb($message);

		$result = $message->toString();
		file_put_contents($this->file, $result);

		return true;
	}
}
