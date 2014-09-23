<?php
namespace Asgard\File;

/**
 * File instance.
 */
class File {
	/**
	 * File path.
	 * @var string
	 */
	protected $src;
	/**
	 * File name.
	 * @var string
	 */
	protected $name;
	/**
	 * Uploaded flag.
	 * @var boolean
	 */
	protected $uploaded;

	/**
	 * Constructor.
	 * @param string $src  file path
	 * @param string $name file name
	 */
	public function __construct($src=null, $name=null) {
		$this->setSrc($src);
		$this->name = $name;
	}

	/**
	 * Set the file path.
	 * @param string $src
	 */
	public function setSrc($src) {
		$this->src = realpath($src);
	}

	/**
	 * Set the file name.
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Get the name basename.
	 * @return string
	 */
	public function getName() {
		if($this->name)
			return $this->name;
		else
			return basename($this->src);
	}

	/**
	 * Set uploaded to a given value.
	 * @param boolean $uploaded
	 */
	public function setUploaded($uploaded) {
		$this->uploaded = $uploaded;
		return $this;
	}

	/**
	 * Check if the file was uploaded.
	 * @return boolean true if the file was uploaded, false otherwise
	 */
	public function isUploaded() {
		return is_uploaded_file($this->src) || $this->uploaded;
	}

	/**
	 * Return the file size.
	 * @return integer
	 */
	public function size() {
		return filesize($this->src);
	}

	/**
	 * Return the file type.
	 * @return string
	 */
	public function type() {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		return finfo_file($finfo, $this->src);
	}

	/**
	 * Return the file extension.
	 * @return string
	 */
	public function extension() {
		if(!$this->getName())
			return;
		return pathinfo($this->getName(), PATHINFO_EXTENSION);
	}

	/**
	 * Check if the file exists.
	 * @return boolean   true if the file exists, false otherwise
	 */
	public function exists() {
		return file_exists($this->src);
	}

	/**
	 * Return the source path.
	 * @return string
	 */
	public function src() {
		return $this->src;
	}

	/**
	 * Get the relative path to a given one.
	 * @param  string $path 
	 * @return string
	 */
	public function relativeTo($path) {
		return \Asgard\File\FileSystem::relativeTo($path, $this->src);
	}

	/**
	 * Format the file path.
	 * @param  string $path
	 * @return string
	 */
	protected function formatPath($path) {
		return preg_replace('/\/|\\\/', DIRECTORY_SEPARATOR, realpath($path));
	}

	/**
	 * Move the file to a directory.
	 * @param  string $dir
	 * @param  integer $mode
	 * @return boolean     return the new file path.
	 */
	public function moveToDir($dir, $mode=null) {
		if($this->isIn($dir))
			return;
		return $this->rename($dir.'/'.$this->getName(), $mode);
	}

	/**
	 * Check if the file is in a given directory.
	 * @param  string  $dir
	 * @return boolean     true if the file is in the directory, false otherwise
	 */
	public function isIn($dir) {
		if(!$this->formatPath($dir))
			return false;
		return strpos($this->formatPath($this->src()), $this->formatPath($dir)) === 0;
	}

	/**
	 * Check if the file is at a given path.
	 * @param  string  $at
	 * @return boolean     true if the file is at the given path, false otherwise
	 */
	public function isAt($at) {
		return $this->formatPath($at) === $this->src;
	}

	/**
	 * Rename the file.
	 * @param  string $dst
	 * @param  integer $mode
	 * @return string    new filename
	 */
	public function rename($dst, $mode=null) {
		if(!$this->src || $this->isAt($dst)) return;
		$filename = \Asgard\File\FileSystem::rename($this->src, $dst, $mode);
		if(!$filename)
			return false;
		$this->src = realpath($filename);
		$this->name = null;
		return $dst;
	}

	/**
	 * Delete the file.
	 * @return boolean     true for success, otherwise false
	 */
	public function delete() {
		if($r = \Asgard\File\FileSystem::delete($this->src)) {
			$this->src = null;
			$this->name = null;
		}
		return $r;
	}

	/**
	 * Copy the file.
	 * @param  string $dst
	 * @param  integer $mode
	 * @return boolean     true for success, otherwise false
	 */
	public function copy($dst, $mode=null) {
		$dst = \Asgard\File\FileSystem::copy($this->src, $dst, $mode);
		if($dst) {
			$copy = clone $this;
			$copy->setSrc($dst);
			return $copy;
		}
		return false;
	}
}