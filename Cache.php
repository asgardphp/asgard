<?php
namespace Coxis\Utils;

class Cache {
	public static function clear() {
		if(\Coxis\Core\App::get('config')->get('cache', 'method') == 'apc') {
			\apc_clear_cache(\Coxis\Core\App::get('config')->get('key').'-'.'user');
		}
		elseif(\Coxis\Core\App::get('config')->get('cache', 'method') == 'file') {
			FileManager::unlink('storage/cache');
		}
	}

	public static function get($file, $default=null) {
		if(\Coxis\Core\App::get('config')->get('phpcache')) {
			if(\Coxis\Core\App::get('config')->get('cache', 'method') == 'apc') {
				$success = null;
				$res = \apc_fetch(\Coxis\Core\App::get('config')->get('key').'-'.$file, $success);
				if($success)
					return $res;
			}
			elseif(\Coxis\Core\App::get('config')->get('cache', 'method') == 'file') {
				try {
					return include 'storage/cache/'.$file.'.php';
				} catch(\ErrorException $e) {}
			}
		}

		if(Tools::is_function($default)) {
			$r = $default();
			static::set($file, $r);
			return $r;
		}
		else
			return $default;
	}

	public static function sizeofvar($var) {
		$start_memory = memory_get_usage();
		$tmp = unserialize(serialize($var));
		return memory_get_usage() - $start_memory;
	}
	
	public static function set($file, $var) {
		if(!\Coxis\Core\App::get('config')->get('phpcache'))
			return;
		if(\Coxis\Core\App::get('config')->get('cache', 'method') == 'apc') {
			apc_store(\Coxis\Core\App::get('config')->get('key').'-'.$file, $var);
		}
		elseif(\Coxis\Core\App::get('config')->get('cache', 'method') == 'file') {
			if(static::sizeofvar($var) > 5*1024*1024)
				return;
			try {
				if(is_object($var))
					$res = 'unserialize(\''.serialize($var).'\')';
				elseif(($ve = var_export($var, true)) == '')
					$res = 'null';
				else
					$res = $ve;
				$res = '<?php'."\n".'return '.$res.';';
				$output = 'storage/cache/'.$file.'.php';
				FileManager::mkdir(dirname($output));
				file_put_contents($output, $res);
			} catch(\ErrorException $e) {
				return false;
			}
		}
		return true;
	}
	
	public static function delete($file) {
		if(\Coxis\Core\App::get('config')->get('cache', 'method') == 'apc') {
			apc_delete(\Coxis\Core\App::get('config')->get('key').'-'.$file);
		}
		elseif(\Coxis\Core\App::get('config')->get('cache', 'method') == 'file') {
			$path = 'storage/cache/'.$file.'.php';
			FileManager::unlink($path);
		}
	}
}