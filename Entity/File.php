<?php
namespace Asgard\Entity;

/**
 * 
 */
class File extends \Asgard\File\File {
	/**
	 * [$web description]
	 * @var boolean
	 */
	protected $web = false;
	/**
	 * [$url description]
	 * @var [type]
	 */
	protected $url;
	/**
	 * [$webDir description]
	 * @var [type]
	 */
	protected $webDir;
	/**
	 * [$toDelete description]
	 * @var [type]
	 */
	protected $toDelete;
	/**
	 * [$dir description]
	 * @var [type]
	 */
	protected $dir;

	/**
	 * [toDelete description]
	 * @param  boolean $toDelete
	 * @return [type]
	 */
	public function toDelete($toDelete=true) {
		$this->toDelete = $toDelete;
	}

	/**
	 * [shouldDelete description]
	 * @return [type]
	 */
	public function shouldDelete() {
		return $this->toDelete;
	}

	/**
	 * [save description]
	 * @return [type]
	 */
	public function save() {
		if($this->web)
			$dir = rtrim(rtrim($this->webDir, '/').'/'.rtrim($this->dir, '/'), '/');
		else
			$dir = rtrim($this->dir, '/');
		$this->rename($dir.'/'.$this->getName(), \Asgard\File\FileSystem::RENAME);
	}

	/**
	 * [__toString description]
	 * @return string
	 */
	public function __toString() {
		if($this->web)
			return $this->url();
		else
			return $this->src();
	}

	/**
	 * [isWeb description]
	 * @return boolean
	 */
	public function isWeb() {
		return $this->web;
	}

	/**
	 * [setWeb description]
	 * @param [type] $web
	 */
	public function setWeb($web) {
		$this->web = $web;
	}

	/**
	 * [setUrl description]
	 * @param [type] $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}

	/**
	 * [url description]
	 * @param  [type] $default
	 * @return [type]
	 */
	public function url($default=null) {
		if(!($src = $this->srcFromWebDir()))
			$src = $default;

		if($this->url)
			return $this->url->to($src);
		else
			return $src;
	}

	/**
	 * [setWebDir description]
	 * @param [type] $webDir
	 */
	public function setWebDir($webDir) {
		$this->webDir = $webDir;
	}

	/**
	 * [srcFromWebDir description]
	 * @return [type]
	 */
	public function srcFromWebDir() {
		if(!$this->isIn($this->webDir))
			return;
		return str_replace($this->formatPath($this->webDir).DIRECTORY_SEPARATOR, '', $this->formatPath($this->src));
	}

	/**
	 * [relativeToWebDir description]
	 * @return [type]
	 */
	public function relativeToWebDir() {
		return $this->relativeTo($this->webDir);
	}

	/**
	 * [setDir description]
	 * @param [type] $dir
	 */
	public function setDir($dir) {
		$this->dir = $dir;
	}
}