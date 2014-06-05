<?php
namespace Asgard\Core;

class Config extends \Asgard\Utils\Bag {
	public function loadConfigDir($dir, $env=null) {
		foreach(glob($dir.'/*.php') as $filename)
			$this->loadConfigFile($filename, $env);
		return $this;
	}
	
	public function loadConfigFile($filename, $env=null) {
		$data = require $filename;
		if(isset($data['all']))
			$this->load($data['all']);
		if(isset($data[$env]))
			$this->load($data[$env]);
		return $this;
	}
}