<?php
namespace Coxis\Core;

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
	
	public function set($str_path, $value) {
		\Coxis\Utils\Tools::pathSet($this->config, $str_path, $value);

		if(\Coxis\Core\App::has('hook'))
			\Coxis\Core\App::get('hook')->trigger(array('Config/Set/'.$str_path, $value));
	}
	
	public function get($str_path) {
		return \Coxis\Utils\Tools::pathGet($this->config, $str_path);
	}
}