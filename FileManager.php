<?php
namespace Asgard\Utils;

class FileManager {
	public static function getNewFileName($output) {
		$fileexts = explode('.', $output);
		$filename = implode('.', array_slice($fileexts, 0, -1));
		$ext = $fileexts[sizeof($fileexts)-1];
		$output = $filename.'.'.$ext;
		
		$i=1;
		while(file_exists($output))
			$output = $filename.'_'.($i++).'.'.$ext;

		return $output;
	}

	public static function move($src, $output) {
		$output = static::getNewFileName($output);
			
		static::mkdir(dirname($output));
			
		if(!copy($src, $output))
			return false;
		else {
			unlink($src);
			return basename($output);
		}
	}

	public static function copy($src, $output) {
		$output = static::getNewFileName($output);
			
		static::mkdir(dirname($output));
			
		return copy($src, $output);
	}

	public static function move_uploaded($src, $output) {
		$output = static::getNewFileName($output);
			
		static::mkdir(dirname($output));
			
		if(!move_uploaded_file($src, $output))
			return false;
		else
			return basename($output);
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
		if(!file_exists($file)) {
			if(file_exists($file))
				$file = $file;
			else
				return false;
		}

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

	public static function put($file, $content) {
		static::mkdir(dirname($file));
		return file_put_contents($file, $content);
	}
}