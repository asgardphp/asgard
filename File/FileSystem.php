<?php
namespace Asgard\File;

class FileSystem {
	const OVERRIDE = 1;
	const RENAME   = 2;
	const IGNORE   = 4;
	const MERGEDIR = 8;

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

	protected static function copyDir($src, $dst, $mode=null) {
		if($mode === null)
			$mode = static::OVERRIDE;

		if($mode & static::RENAME)
			$dst = static::getNewFilename($dst);
		elseif($mode & static::OVERRIDE)
			static::delete($dst);
		elseif(!($mode & static::MERGEDIR) && file_exists($dst))
			return false;

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

	public static function delete($file) {
		if(!file_exists($file))
			return false;

		if(is_dir($file))
			static::deleteDir($file);
		else
			unlink($file);
		
		return true;
	}
	
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
	
	public static function mkdir($dir) {
		if(!file_exists($dir))
			return mkdir($dir, 0777, true);
		return true;
	}

	public static function write($dst, $content, $mode=null) {
		if($mode === null)
			$mode = static::OVERRIDE;

		if($mode & static::RENAME)
			$dst = static::getNewFilename($dst);
		elseif($mode & static::OVERRIDE)
			static::delete($dst);
		elseif(file_exists($dst))
			return false;

		static::mkdir(dirname($dst));
		$r = file_put_contents($dst, $content);

		if($r !== false)
			return $dst;
		return false;
	}
}