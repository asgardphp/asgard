<?php
namespace Coxis\Utils;

class Log {
	public static function add($filename, $msg) {
		\Coxis\Utils\FileManager::mkdir(dirname('storage/logs/'.$filename));
		$filename = \Coxis\Utils\FileManager::getNewFileName('storage/logs/'.$filename);
		file_put_contents($filename, $msg);
	}
	
	public static function write($filename, $msg) {
		\Coxis\Utils\FileManager::mkdir(dirname('storage/logs/'.$filename));
		file_put_contents('storage/logs/'.$filename, "\n".$msg, FILE_APPEND|LOCK_EX);
	}
}