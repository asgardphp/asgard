<?php
namespace Asgard\Common;

class FileManager {
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

	public static function getNewFileName($dst) {
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

	public static function move($src, $dst, $rename=false) {
		if($rename)
			$dst = static::getNewFileName($dst);
			
		static::mkdir(dirname($dst));
			
		if(!copy($src, $dst))
			return false;
		else {
			unlink($src);
			return basename($dst);
		}
	}

	public static function copy($src, $dst, $rename=false) {
		if(is_dir($src))
			return static::copyDir($src, $dst, $rename);
		else {
			if($rename)
				$dst = static::getNewFileName($dst);
			static::mkdir(dirname($dst));
			$r = copy($src, $dst);
			if($rename && $r)
				return $dst;
			return $r;
		}
	}

	protected static function copyDir($src, $dst, $rename=false) { 
		$r = true;
		$dir = opendir($src);
		if($rename)
			$dst = static::getNewFileName($dst);
		static::mkdir($dst);
		while(false !== ($file = readdir($dir))) { 
			if(($file != '.') && ($file != '..')) { 
				if(is_dir($src.'/'.$file))
					$r = $r && static::copyDir($src.'/'.$file,$dst.'/'.$file); 
				else
					$r = $r && copy($src.'/'.$file,$dst.'/'.$file); 
			} 
		} 
		closedir($dir);
		if($rename)
			return $dst;
		return $r;
	} 

	public static function move_uploaded($src, $dst) {
		$dst = static::getNewFileName($dst);
			
		static::mkdir(dirname($dst));
			
		if(!move_uploaded_file($src, $dst))
			return false;
		else
			return basename($dst);
	}
	
	public static function isUploaded($file) {
		return (isset($file['tmp_name']) && !empty($file['tmp_name']));
	}
	
	public static function rmdir($directory, $empty=FALSE) {
		if(substr($directory,-1) == '/')
			$directory = substr($directory,0,-1);
		if(!file_exists($directory) || !is_dir($directory))
			return FALSE;
		elseif(is_readable($directory)) {
			$handle = opendir($directory);
			while (FALSE !== ($item = readdir($handle))) {
				if($item != '.' && $item != '..') {
					$path = $directory.'/'.$item;
					if(is_dir($path))
						static::rmdir($path);
					else
						unlink($path);
				}
			}
			closedir($handle);
			if($empty == FALSE)
				if(!rmdir($directory))
					return FALSE;
		}
		return TRUE;
	}

	public static function unlink($file) {
		if(!file_exists($file))
			return false;

		if(is_dir($file))
			static::rmdir($file);
		else
			unlink($file);
		
		return true;
	}
	
	public static function mkdir($dir) {
		if(!file_exists($dir))
			return mkdir($dir, 0777, true);
		return true;
	}

	public static function put($dst, $content, $rename=false) {
		if($rename)
			$dst = static::getNewFileName($dst);
		static::mkdir(dirname($dst));
		$r = file_put_contents($dst, $content);
		if($r && $rename)
			return $dst;
		return $r;
	}
}