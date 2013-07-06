<?php
namespace Coxis\Core;

//todo merge with coxis?
class Config {
	protected $config = array();
	
	function __construct() {
		$this->loadConfigDir('config');
		\Hook::hookOn(array('config', 'set', 'error_display'), function($chain, $value) {
			ini_set('display_errors', $value);
		});
	}

	public function loadConfigDir($dir) {
		foreach(glob(_DIR_.$dir.'/*.php') as $filename)
			$this->loadConfigFile($filename);
	}
	
	public function loadConfigFile($filename) {
		require($filename);
		if(isset($config['all']))
			$this->load($config['all']);
		if(isset($config[_ENV_]))
			$this->load($config[_ENV_]);
	}
	
	public function load($config) {
		foreach($config as $key=>$value)
			$this->set($key, $value);
	}
	
	public function set() {
		$args = func_get_args();
		$arr =& $this->config;
		$key = $args[sizeof($args)-2];
		$value = $args[sizeof($args)-1];
		array_pop($args);
		array_pop($args);
		
		foreach($args as $parent)
			$arr =& $arr[$parent];
		\Hook::trigger(array_merge(array('config', 'set'), array_merge($args, array($key))), array($value));
		$arr[$key] = $value;
	}
	
	public function get() {
		//todo use \get()
		$args = func_get_args();
		$result = $this->config;
		foreach(func_get_args() as $key)
			if(!isset($result[$key]))
				return null;
			else
				$result = $result[$key];
		
		return $result;
	}
}