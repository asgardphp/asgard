<?php
namespace Asgard\Entity;

class File extends \Asgard\File\File {
	protected $web = false;
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

	public function save() {
		if($this->web)
			$dir = rtrim(rtrim($this->webDir, '/').'/'.rtrim($this->dir, '/'), '/');
		else
			$dir = rtrim($this->dir, '/');
		$this->rename($dir.'/'.$this->getName(), \Asgard\File\FileSystem::RENAME);
	}

	public function __toString() {
		if($this->web)
			return $this->url();
		else
			return $this->src();
	}

	/**** WEB ***/
	public function isWeb() {
		return $this->web;
	}

	public function setWeb($web) {
		$this->web = $web;
	}

	public function setUrl($url) {
		$this->url = $url;
	}

	public function url($default=null) {
		if(!($src = $this->srcFromWebDir()))
			$src = $default;

		if($this->url)
			return $this->url->to($src);
		else
			return $src;
	}

	public function setWebDir($webDir) {
		$this->webDir = $webDir;
	}

	public function srcFromWebDir() {
		if(!$this->isIn($this->webDir))
			return;
		return str_replace($this->formatPath($this->webDir).DIRECTORY_SEPARATOR, '', $this->formatPath($this->src));
	}

	public function relativeToWebDir() {
		return $this->relativeTo($this->webDir);
	}

	public function setDir($dir) {
		$this->dir = $dir;
	}
	/**** /WEB ***/
}