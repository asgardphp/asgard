<?php
namespace Coxis\Core;

//todo merge with coxis?
class Config {
	protected $config = array();
	
	function __construct($dir=null) {
		if($dir)
			$this->loadConfigDir($dir);
	}

	public function loadConfigDir($dir) {
		foreach(glob(_DIR_.$dir.'/*.php') as $filename)
			$this->loadConfigFile($filename);
	}
	
	public function loadConfigFile($filename) {
		$config = require $filename;
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
		if(\Coxis\Core\App::has('hook'))
			\Coxis\Core\App::get('hook')->trigger(array_merge(array('config', 'set'), array_merge($args, array($key))), array($value));
		$arr[$key] = $value;
	}
	
	public function get() {
		return call_user_func_array(array('\Coxis\Utils\Tools', 'get'), array_merge(array($this->config), func_get_args()));
	}
}