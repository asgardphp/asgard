<?php
namespace Asgard\File;

class File {
	protected $src;
	protected $name;

	public function __construct($src=null, $name=null) {
		$this->setSrc($src);
		$this->name = $name;
	}

	public function setSrc($src) {
		$this->src = realpath($src);
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getName() {
		if($this->name)
			return $this->name;
		else
			return basename($this->src);
	}

	public function isUploaded() {
		return is_uploaded_file($this->src);
	}

	public function size() {
		return filesize($this->src);
	}

	public function type() {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		return finfo_file($finfo, $this->src);
	}

	public function extension() {
		if(!$this->getName())
			return;
		return pathinfo($this->getName(), PATHINFO_EXTENSION);
	}

	public function exists() {
		return file_exists($this->src);
	}

	public function src() {
		return $this->src;
	}

	public function relativeTo($path) {
		return \Asgard\File\FileSystem::relativeTo($this->src, $path);
	}

	protected function formatPath($path) {
		return preg_replace('/\/|\\\/', DIRECTORY_SEPARATOR, realpath($path));
	}

	public function moveToDir($dir, $mode=null) {
		if($this->isIn($dir))
			return;
		return $this->rename($dir.'/'.$this->getName(), $mode);
	}

	public function isIn($dir) {
		if(!$this->formatPath($dir))
			return false;
		return strpos($this->formatPath($this->src()), $this->formatPath($dir)) === 0;
	}

	public function isAt($at) {
		return $this->formatPath($at) === $this->src;
	}

	public function rename($dst, $mode=null) {
		if(!$this->src || $this->isAt($dst)) return;
		$filename = \Asgard\File\FileSystem::rename($this->src, $dst, $mode);
		if(!$filename)
			return false;
		$this->src = realpath($filename);
		$this->name = null;
		return $dst;
	}

	public function delete() {
		if($r = \Asgard\File\FileSystem::delete($this->src)) {
			$this->src = null;
			$this->name = null;
		}
		return $r;
	}

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