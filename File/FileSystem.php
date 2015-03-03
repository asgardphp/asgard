<?php
namespace Asgard\File;

/**
 * File system.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class FileSystem {
	/**
	 * Modes
	 * OVERRIDE: override existing files
	 * RENAME: rename new files
	 * IGNORE: ignore new files
	 * MERGEDIR: merge directories
	 */
	const OVERRIDE = 1;
	const RENAME   = 2;
	const IGNORE   = 4;
	const MERGEDIR = 8;

	/**
	 * Get the relative path.
	 * @param  string $from
	 * @param  string $to
	 * @return string
	 */
	public static function relativeTo($from, $to) {
		$from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
		$to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
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

	/**
	 * Get a new unique filename if file exists.
	 * @param  string $dst
	 * @return string
	 */
	public static function getNewFilename($dst) {
		$fileexts = explode('.', $dst);
		if(count($fileexts) > 1) {
			$filename = implode('.', array_slice($fileexts, 0, -1));
			$ext = $fileexts[count($fileexts)-1];
			$dst = $filename.'.'.$ext;
		}
		else
			$filename = $dst;

		$i=1;
		while(file_exists($dst))
			$dst = $filename.'_'.($i++).(isset($ext) ? '.'.$ext:'');

		return $dst;
	}

	/**
	 * Rename a file.
	 * @param  string $src
	 * @param  string $dst
	 * @param  integer $mode
	 * @return boolean     true for success, otherwise false
	 */
	public static function rename($src, $dst, $mode=null) {
		if($mode === null)
			$mode = static::OVERRIDE;

		if(!($finalDst = static::copy($src, $dst, $mode)))
			return false;
		else {
			static::delete($src);
			return $finalDst;
		}
	}

	/**
	 * Copy a file.
	 * @param  string $src
	 * @param  string $dst
	 * @param  integer $mode
	 * @return boolean     true for success, otherwise false
	 */
	public static function copy($src, $dst, $mode=null) {
		if($mode === null)
			$mode = static::OVERRIDE;

		if(is_dir($src))
			return static::copyDir($src, $dst, $mode);
		else {
			if($mode & static::RENAME)
				$dst = static::getNewFilename($dst);
			elseif($mode & static::OVERRIDE)
				static::delete($dst);
			elseif(file_exists($dst))
				return false;

			static::mkdir(dirname($dst));
			$r = copy($src, $dst);

			if($r !== false)
				return $dst;
			return false;
		}
	}

	/**
	 * Copy a directory.
	 * @param  string $src
	 * @param  string $dst
	 * @param  integer $mode
	 * @return boolean     true for success, otherwise false
	 */
	protected static function copyDir($src, $dst, $mode=null) {
		if($mode === null)
			$mode = static::OVERRIDE;

		if(file_exists($dst)) {
			if($mode & static::MERGEDIR) {}
			elseif($mode & static::RENAME)
				$dst = static::getNewFilename($dst);
			elseif($mode & static::OVERRIDE)
				static::delete($dst);
		}

		$r = true;
		$dir = opendir($src);
		static::mkdir($dst);
		while(false !== ($file = readdir($dir))) {
			if(($file != '.') && ($file != '..'))
				$r = $r && static::copy($src.'/'.$file, $dst.'/'.$file, $mode);
		}
		closedir($dir);

		if($r !== false)
			return $dst;
		return false;
	}

	/**
	 * Delete a file.
	 * @param  string $file
	 * @return boolean      true for success, false otherwise
	 */
	public static function delete($file) {
		if(!file_exists($file))
			return false;

		if(is_dir($file))
			static::deleteDir($file);
		else
			unlink($file);

		return true;
	}

	/**
	 * Delete a directory.
	 * @param  string $directory
	 * @return boolean     true for success, otherwise false
	 */
	protected static function deleteDir($directory) {
		if(substr($directory,-1) == '/')
			$directory = substr($directory, 0, -1);
		if(!file_exists($directory) || !is_dir($directory))
			return false;
		elseif(is_readable($directory)) {
			$handle = opendir($directory);
			while(false !== ($item = readdir($handle))) {
				if($item != '.' && $item != '..') {
					$path = $directory.'/'.$item;
					if(is_dir($path))
						static::deleteDir($path);
					else
						unlink($path);
				}
			}
			closedir($handle);
			if(!rmdir($directory))
				return false;
		}
		return true;
	}

	/**
	 * Make a directory.
	 * @param  string $dir
	 * @return boolean     true for success, otherwise false
	 */
	public static function mkdir($dir) {
		if(!file_exists($dir))
			return mkdir($dir, 0777, true);
		return true;
	}

	/**
	 * Write into a file.
	 * @param  string $dst
	 * @param  string $content
	 * @param  integer $mode
	 * @param  boolean $append
	 * @return boolean      true for success, otherwise false
	 */
	public static function write($dst, $content, $mode=null, $append=false) {
		if($mode === null)
			$mode = static::OVERRIDE;

		if($mode & static::RENAME)
			$dst = static::getNewFilename($dst);
		elseif($mode & static::OVERRIDE)
			static::delete($dst);
		elseif(file_exists($dst))
			return false;

		static::mkdir(dirname($dst));
		$r = file_put_contents($dst, $content, $append ? FILE_APPEND:0);

		if($r !== false)
			return $dst;
		return false;
	}
}