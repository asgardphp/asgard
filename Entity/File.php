<?php
namespace Asgard\Entity;

class File extends \Asgard\Files\File {
	protected $url;
	protected $webDir;
	protected $toDelete;
	protected $dir;

	public function toDelete($toDelete=true) {
		$this->toDelete = $toDelete;
	}

	public function shouldDelete() {
		return $this->toDelete;
	}

	public function setUrl($url) {
		$this->url = $url;
	}

	public function url($default=null) {
		$webDir = $this->formatPath($this->webDir);
		if($this->exists() && strpos($this->src, $webDir) === 0)
			$src = str_replace($webDir.DIRECTORY_SEPARATOR, '', $this->formatPath($this->src));
		else
			$src = $default;

		if($this->url)
			return $this->url->to($src);
		else
			return $src;
	}

	public function setWebDir($webDir) {
		$this->webDir = realpath($webDir);
	}

	public function srcFromWebDir() {
		if(!$this->isIn($this->webDir))
			return;
		return str_replace($this->formatPath($this->webDir).DIRECTORY_SEPARATOR, '', $this->formatPath($this->src));
	}

	public function relativeToWebDir() {
		return $this->relativeTo($this->webDir);
	}

	public function __toString() {
		return $this->url();
	}

	public function setDir($dir) {
		$this->dir = $dir;
	}

	public function save() {
		$dir = trim(trim($this->webDir, '/').'/'.trim($this->dir, '/'), '/');
		$this->move($dir.'/'.$this->getName(), true);
	}
}