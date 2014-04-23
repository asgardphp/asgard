<?php
namespace Asgard\Core\Cli;

class Request {
	protected $route;
	public $args;

	public function __construct($route) {
		$this->route = $route;
		$this->args = new \Asgard\Core\Cli\Args;
	}

	public function getRoute() {
		return $this->route;
	}

	public function getEnvironment() {
		if(isset($this->args['dev']))
			return $this->args['dev'];
		else
			return 'dev';
	}

	public static function createFromGlobals() {
		global $argv;

		array_shift($argv);

		$request = new static($argv[0]);
		array_shift($argv);
		$request->args->_setAll(static::parseArgs($argv));

		return $request;
	}
	
	protected static function parseArgs($argv) {
		$res = array();
		for($k=0; $k<sizeof($argv); $k++) {
			$v = $argv[$k];
			if(preg_match('/^--([^ =]+)=(.+)/', $v, $matches))
				$res[$matches[1]] = $matches[2];
			elseif(preg_match('/^--(.+)/', $v, $matches)) {
				if(isset($argv[++$k]))
					$res[$matches[1]] = $argv[$k];
				else
					$res[$matches[1]] = true;
			}
			elseif(preg_match('/^-(.+)/', $v, $matches)) {
				if(isset($argv[++$k]))
					$res[$matches[1]] = $argv[$k];
				else
					$res[$matches[1]] = true;
			}
			else
				$res[] = $v;
		}		
		return $res;
	}
}