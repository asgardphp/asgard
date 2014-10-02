<?php
namespace Asgard\Config;

/**
 * Configuration bag.
 */
interface ConfigInterface extends \Asgard\Common\BagInterface {
	/**
	 * Load a directory.
	 * @param  string          $dir
	 * @param  string          $env
	 * @return ConfigInterface $this
	 */
	public function loadDir($dir, $env=null);

	/**
	 * Load a file.
	 * @param  string          $filename
	 * @return ConfigInterface $this
	 */
	public function loadFile($filename);
}