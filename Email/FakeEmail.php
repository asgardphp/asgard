<?php
namespace Asgard\Email;

class FakeEmail implements DriverInterface {
	protected $file;

	public function transport($file) {
		$this->file = $file;
	}

	public function send($cb) {
		$message = new SwiftMessage();

		$cb($message);

		$result = $message->toString();
		file_put_contents($this->file, $result);

		return true;
	}
}
