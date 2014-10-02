<?php
namespace Asgard\Common;

/**
 * Class to zip files.
 * @api
 */
class Zip {
	/**
	 * Zipper.
	 * @param  string $source
	 * @param  string $destination
	 * @return boolean true for success
	 * @api
	 */
	public static function zip($source, $destination) {
	    if(!extension_loaded('zip') || !file_exists($source))
			return false;

	    $zip = new \ZipArchive();
	    if(!$zip->open($destination, \ZIPARCHIVE::CREATE))
			return false;

	    $source = str_replace('\\', '/', realpath($source));

	    if(is_dir($source)) {
			$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);

			foreach($files as $file) {
		    	$file = str_replace('\\', '/', realpath($file));

			    if(is_dir($file))
					$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
			    elseif(is_file($file))
					$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
			}
	    }
	    elseif(is_file($source))
			$zip->addFromString(basename($source), file_get_contents($source));

	    return $zip->close();
	}
}