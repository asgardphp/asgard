<?php
namespace Asgard\Files;

class File {
	protected $url;
	protected $src;
	protected $name;
	protected $webDir;

	public function __construct($src=null, $name=null, $webDir=null, $url=null) {
		$this->setSrc($src);
		$this->name = $name;
		if($webDir)
			$this->webDir = realpath($webDir);
		$this->url = $url;
	}

	public function setSrc($src) {
		$this->src = realpath($src);
	}

	public function setUrl($url) {
		$this->url = $url;
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

	public function setWebDir($webDir) {
		$this->webDir = realpath($webDir);
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

	public function srcFromWebDir() {
		if(!$this->isIn($this->webDir))
			return;
		return str_replace($this->formatPath($this->webDir).DIRECTORY_SEPARATOR, '', $this->formatPath($this->src));
	}

	public function relativeToWebDir() {
		return $this->relativeTo($this->webDir);
	}

	public function relativeTo($path) {
		$from = is_dir($path) ? rtrim($path, '\/') . '/' : $path;
		$to   = is_dir($this->src)   ? rtrim($this->src, '\/') . '/'   : $this->src;
		$from = str_replace('\\', '/', $from);
		$to   = str_replace('\\', '/', $to);

		$from     = explode('/', $from);
		$to       = explode('/', $to);
		$relPath  = $to;

		foreach($from as $depth => $dir) {
			if($dir === $to[$depth])
				array_shift($relPath);
			else {
				$remaining = count($from) - $depth;
				if($remaining > 1) {
					$padLength = (count($relPath) + $remaining - 1) * -1;
					$relPath = array_pad($relPath, $padLength, '..');
					break;
				}
			}
		}
		return implode('/', $relPath);
	}

	protected function formatPath($path) {
		return preg_replace('/\/|\\\/', DIRECTORY_SEPARATOR, realpath($path));
	}

	public function url($default=null) {
		$webDir = $this->webDir;
		if($this->exists() && strpos($this->src, $webDir) === 0)
			$src = $this->src;
		else
			$src = $default;
		if($this->url)
			return $this->url->to(str_replace($webDir.DIRECTORY_SEPARATOR, '', $src));
		else
			return str_replace($webDir.DIRECTORY_SEPARATOR, '', $src);
	}

	public function moveToDir($dir, $rename=true) {
		if($this->isIn($dir))
			return;
		return $this->move($dir.'/'.$this->getName(), $rename);
	}

	public function isIn($dir) {
		if(!$this->formatPath($dir))
			return false;
		return strpos($this->formatPath($this->src()), $this->formatPath($dir)) === 0;
	}

	public function isAt($at) {
		return $this->formatPath($at) === $this->src;
	}

	public function move($dst, $rename=true) {
		if(!$this->src || $this->isAt($dst)) return;
		$filename = \Asgard\Utils\FileManager::move($this->src, $dst, $rename);
		if(!$filename)
			return false;
		$this->src = realpath(dirname($dst).'/'.$filename);
		return $dst;
	}

	public function delete() {
		if($r = \Asgard\Utils\FileManager::unlink($this->src))
			$this->src = null;
		return $r;
	}

	public function copy($dst, $rename=true) {
		$dst = \Asgard\Utils\FileManager::copy($this->src, $dst, $rename);
		if($dst) {
			$copy = clone $this;
			$copy->setSrc($dst);
			return $copy;
		}
		return false;
	}

	public function __toString() {
		return $this->url();
	}
}