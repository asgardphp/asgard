<?php
namespace Asgard\Http;

/**
 * Http file.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class HttpFile extends \Asgard\File\File {
	/**
	 * File type.
	 * @var string
	 */
	protected $type;
	/**
	 * File size.
	 * @var integer
	 */
	protected $size;
	/**
	 * Error code.
	 * @var integer
	 */
	protected $error;

	/**
	 * Constructor.
	 * @param string  $src
	 * @param string  $name
	 * @param string  $type
	 * @param integer $size
	 * @param integer $error
	 */
	public function __construct($src, $name, $type, $size, $error) {
		$this->type  = $type;
		$this->size  = $size;
		$this->error = $error;
		parent::__construct($src, $name);
		$this->setUploaded(true);
	}

	/**
	 * Create file object from array.
	 * @param  array  $arr
	 * @return HttpFile
	 */
	public static function createFromArray(array $arr) {
		return new static($arr['tmp_name'], $arr['name'], $arr['type'], $arr['size'], $arr['error']);
	}

	/**
	 * Set the file type.
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * Get the file type.
	 * @return string
	 */
	public function type() {
		return $this->type;
	}

	/**
	 * Set the file size.
	 * @param integer $size
	 */
	public function setSize($size) {
		$this->size = $size;
	}

	/**
	 * Get the file size.
	 * @return string
	 */
	public function size() {
		return $this->size;
	}

	/**
	 * Set the file error code.
	 * @param integer $error
	 */
	public function setError($error) {
		$this->error = $error;
	}

	/**
	 * Get the file error code.
	 * @return integer
	 */
	public function error() {
		return $this->error;
	}
}