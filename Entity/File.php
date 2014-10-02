<?php
namespace Asgard\Entity;

/**
 * Entity file.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class File extends \Asgard\File\File {
	/**
	 * Flag for file accessible from web.
	 * @var boolean
	 */
	protected $web = false;
	/**
	 * URL dependency.
	 * @var \Asgard\Http\URLInterface
	 */
	protected $url;
	/**
	 * Web directory.
	 * @var string
	 */
	protected $webDir;
	/**
	 * toDelete flag.
	 * @var boolean
	 */
	protected $toDelete;
	/**
	 * Destination directory.
	 * @var string
	 */
	protected $dir;

	/**
	 * Set toDelete flag.
	 * @param  boolean $toDelete
	 */
	public function toDelete($toDelete=true) {
		$this->toDelete = $toDelete;
	}

	/**
	 * Check if should delete the file.
	 * @return boolean
	 */
	public function shouldDelete() {
		return $this->toDelete;
	}

	/**
	 * Save the file.
	 */
	public function save() {
		if($this->web)
			$dir = rtrim(rtrim($this->webDir, '/').'/'.rtrim($this->dir, '/'), '/');
		else
			$dir = rtrim($this->dir, '/');
		$this->rename($dir.'/'.$this->getName(), \Asgard\File\FileSystem::RENAME);
	}

	/**
	 * __toString magic method. Print out the url or source.
	 * @return string
	 */
	public function __toString() {
		if($this->web)
			return $this->url();
		else
			return $this->src();
	}

	/**
	 * Check if file is accessible from web.
	 * @return boolean
	 */
	public function isWeb() {
		return $this->web;
	}

	/**
	 * Set web flag.
	 * @param boolean $web
	 */
	public function setWeb($web) {
		$this->web = $web;
	}

	/**
	 * Set URL dependency.
	 * @param \Asgard\Http\URLInterface $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}

	/**
	 * Return the file url.
	 * @param  string $default
	 * @return string
	 */
	public function url($default=null) {
		if($this->isIn($this->webDir))
			$src = $this->relativeToWebDir();
		else
			$src = $default;

		if($this->url && $src)
			return $this->url->to($src);
		else
			return $src;
	}

	/**
	 * Set web directory.
	 * @param string $webDir
	 */
	public function setWebDir($webDir) {
		$this->webDir = $webDir;
	}

	/**
	 * Return relative path from web directory.
	 * @return string
	 */
	public function relativeToWebDir() {
		return $this->relativeTo($this->webDir);
	}

	/**
	 * Set destination directory.
	 * @param string $dir
	 */
	public function setDir($dir) {
		$this->dir = $dir;
	}
}