<?php
namespace Asgard\Http;

class HttpFile extends \Asgard\File\File {
	protected $type;
	protected $size;
	protected $error;

	public function __construct($src, $name, $type, $size, $error) {
		$this->type = $type;
		$this->size = $size;
		$this->error = $error;
		parent::__construct($src, $name);
		$this->setUploaded(true);
	}

	public static function createFromArray(array $arr) {
		return new static($arr['tmp_name'], $arr['name'], $arr['type'], $arr['size'], $arr['error']);
	}

	public function setType($type) {
		$this->type = $type;
	}

	public function type() {
		return $this->type;
	}

	public function setSize($size) {
		$this->size = $size;
	}

	public function size() {
		return $this->size;
	}

	public function setError($error) {
		$this->error = $error;
	}

	public function error() {
		return $this->error;
	}
}