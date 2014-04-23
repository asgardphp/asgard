<?php
namespace Asgard\Core\Cli;

class AsgardController extends CLIController {
	public function versionAction($request) {
		echo 'Version: alpha';
	}

	public function ccAction($request) {
		Cache::clear();
	}

	public function getAction($request) {
		$browser = new \Asgard\Utils\Browser;
		echo $browser->get($request[0]);
	}

	public function publishAction($request) {
		$bundle = $request[0];
		static::publishBundle($bundle);
	}

	public static function publishBundle($bundle_path) {
		$bundle = basename($bundle_path);
		echo 'Publishing assets of bundle '.$bundle.'..'."\n";

		if(!file_exists($bundle_path))
			$bundle_path = _DIR_.$bundle_path;

		if(file_exists($bundle_path.'/web') && is_dir($bundle_path.'/web'))
			static::copyDir($bundle_path.'/web', 'web');
	}

	public function consoleAction($request) {
		echo 'Starting console mode..'."\n";
		echo 'Type quit to quit.'."\n";
		$fh = fopen('php://stdin', 'r');
		echo '>';
		$cmd = fgets($fh, 1024);
		while($cmd != "quit\r\n") {
			$res = null;
			if(!preg_match('/;$/', $cmd))
				$cmd .= ';';

			$worked = @eval('$res = '.$cmd);
			if($worked !== null) {
				$worked = @eval($cmd);
				if($worked !== null)
					echo 'Invalid command'."\n";
			}

			ob_start();
			var_dump($res);
			$res_dump = ob_get_clean();
			echo "\n".'Result: '.$res_dump;
			echo "\n";
			echo '>';
			$cmd = fgets($fh, 1024);
		}
		echo 'Quit..'."\n";
	}

	public static function installAction($request) {
		$bundle_path = $request[0];
		\BundlesManager::loadentityFixtures($bundle_path);

		static::publishBundle($bundle_path);

		if(file_exists(_DIR_.$bundle_path.'install.php'))
			include($bundle_path.'install.php');
	}
	
	public function backupfilesAction($request) {
		if(isset($request[0]))
			$output = $request[0];
		else
			$output = 'backup/files/'.time().'.zip';
		echo 'Dumping files into '.$output;
		
		$path = 'web/upload';
		FileManager::mkdir(dirname($output));
		Tools::zip($path, $output);
	}
	
	public function buildAction($request) {
		$input = $request[0];
	
		$yaml = new \Symfony\Component\Yaml\Parser();
		$raw = $yaml->parse(file_get_contents($input));
		$bundles = array();
		foreach($raw as $name=>$bundle) {
			if(file_exists(_DIR_.'app/'.$name.'/')) {
				static::promptConfirmation('Some bundles already exist. Are you sure to continue?');
				break;
			}
		}
		
		foreach($raw as $bundle_name=>$raw_bundle) {
			if(file_exists(_DIR_.'app/'.$bundle_name.'/'))
				\Asgard\Utils\FileManager::rmdir('app/'.$bundle_name.'/');
			
			$bundle = $raw_bundle;
			$bundle['name'] = strtolower($bundle_name);
			$bundle['namespace'] = 'App\\'.ucfirst($bundle['name']);
			
			if(!isset($bundle['entities']))
				$bundle['entities'] = array();
			if(!isset($bundle['controllers']))
				$bundle['controllers'] = array();

			foreach($bundle['entities'] as $name=>$entity) {
				if(!isset($bundle['entities'][$name]['meta']))
					$bundle['entities'][$name]['meta'] = array();
				if(isset($bundle['entities'][$name]['meta']['name']))
					$bundle['entities'][$name]['meta']['name'] = strtolower($bundle['entities'][$name]['meta']['name']);
				else
					$bundle['entities'][$name]['meta']['name'] = $name;

				$bundle['entities'][$name]['meta']['entityClass'] = $bundle['namespace'].'\Entities\\'.ucfirst($name);

				if(isset($bundle['entities'][$name]['meta']['plural']))
					$bundle['entities'][$name]['meta']['plural'] = strtolower($bundle['entities'][$name]['meta']['plural']);
				else
					$bundle['entities'][$name]['meta']['plural'] = $bundle['entities'][$name]['meta']['name'].'s';
				if(isset($bundle['entities'][$name]['meta']['label']))
					$bundle['entities'][$name]['meta']['label'] = strtolower($bundle['entities'][$name]['meta']['label']);
				else
					$bundle['entities'][$name]['meta']['label'] = $bundle['entities'][$name]['meta']['name'];
				if(isset($bundle['entities'][$name]['meta']['label_plural']))
					$bundle['entities'][$name]['meta']['label_plural'] = strtolower($bundle['entities'][$name]['meta']['label_plural']);
				else
					$bundle['entities'][$name]['meta']['label_plural'] = $bundle['entities'][$name]['meta']['label'].'s';
				if(!isset($bundle['entities'][$name]['meta']['name_field'])) {
					$properties = array_keys($bundle['entities'][$name]['properties']);
					$bundle['entities'][$name]['meta']['name_field'] = $properties[0];
				}
					
				if(!isset($bundle['entities'][$name]['properties']))
					$bundle['entities'][$name]['properties'] = array();
				if(!isset($bundle['entities'][$name]['relations']))
					$bundle['entities'][$name]['relations'] = array();
				if(!isset($bundle['entities'][$name]['behaviors']))
					$bundle['entities'][$name]['behaviors'] = array();

				foreach($bundle['entities'][$name]['properties'] as $k=>$v) {
					if(!$v)
						$bundle['entities'][$name]['properties'][$k] = array();
				}

				if(!isset($bundle['entities'][$name]['front']))
					$bundle['entities'][$name]['front'] = array();
				if(!is_array($bundle['entities'][$name]['front'])) 
					$bundle['entities'][$name]['front'] = array('index', 'details');
			}

			foreach($bundle['controllers'] as $name=>$controller) {
				$bundle['controllers'][$name]['name'] = $name;
				if(!isset($bundle['controllers'][$name]['prefix']))
					$bundle['controllers'][$name]['prefix'] = null;
				if(!isset($bundle['controllers'][$name]['actions']))
					$bundle['controllers'][$name]['actions'] = array();
				foreach($bundle['controllers'][$name]['actions'] as $aname=>$action) {
					if(!isset($bundle['controllers'][$name]['actions'][$aname]['template']))
						$bundle['controllers'][$name]['actions'][$aname]['template'] = strtolower($aname).'.php';
					if(!isset($bundle['controllers'][$name]['actions'][$aname]['route']))
						$bundle['controllers'][$name]['actions'][$aname]['route'] = null;
				}
			}

			$bundles[$bundle_name] = $bundle;
		}

		foreach($bundles as $name=>$bundle) {
			$dst = 'app/'.strtolower($name).'/';
			static::processFile(__dir__.'/bundle_template/Bundle.php', $dst.'Bundle.php', array('bundle'=>$bundle));
			foreach($bundle['entities'] as $name=>$entity) {
				static::processFile(__dir__.'/bundle_template/entities/_Entity.php', $dst.'entities/'.ucfirst($bundle['entities'][$name]['meta']['name']).'.php', array('bundle'=>$bundle, 'entity'=>$entity));
				static::processFile(__dir__.'/bundle_template/controllers/_EntityController.php', $dst.'controllers/'.ucfirst($bundle['entities'][$name]['meta']['name']).'Controller.php', array('bundle'=>$bundle, 'entity'=>$entity));
				static::processFile(__dir__.'/bundle_template/views/_entity/index.php', $dst.'views/'.$bundle['entities'][$name]['meta']['name'].'/index.php', array('bundle'=>$bundle, 'entity'=>$entity));
				static::processFile(__dir__.'/bundle_template/views/_entity/details.php', $dst.'views/'.$bundle['entities'][$name]['meta']['name'].'/details.php', array('bundle'=>$bundle, 'entity'=>$entity));
			}

			foreach($bundle['controllers'] as $name=>$controller) {
				static::processFile(__dir__.'/bundle_template/controllers/_Controller.php', $dst.'controllers/'.$controller['name'].'.php', array('bundle'=>$bundle, 'controller'=>$controller));
				foreach($controller['actions'] as $action) {
					if($action['template'])
						\Asgard\Utils\FileManager::put($dst.'views/'.strtolower(preg_replace('/Controller$/', '', $controller['name'])).'/'.$action['template'], '');
				}
			}

			\Asgard\Core\App::get('hook')->trigger('Agard\CLI\generator\bundleBuild', array(&$bundle, 'app/'.strtolower($bundle['name']).'/'));
		}
			
		echo 'Bundles created: '.implode(', ', array_keys($bundles));
	}
	
	public static function processFile($_src, $_dst, $vars) {
		foreach($vars as $k=>$v)
			$$k = $v;

		ob_start();
		include $_src;
		$content = ob_get_contents();
		ob_end_clean();

		$content = str_replace('<%', '<?php', $content);
		$content = str_replace('%>', '?>', $content);

		\Asgard\Utils\FileManager::put($_dst, $content);
	}
 
	protected static function promptConfirmation($msg) {
		if(!function_exists('getinput'))
			return;
		echo $msg.' (y/n)';
		$reply = strtolower(\getinput());
		if($reply != 'y')
			die('OK!');
	}

	public static function outputPHP($value) {
		if(is_array($value) && $value === array_values($value)) {
			$res = 'array('."\n";
			foreach($value as $v)
				$res .= "\t".static::outputPHP($v).",\n";
			$res .= ');';
			return $res;
		}
		else
			return var_export($value, true);
	}

	public function generateTestsAction($request) {
		if(isset($request['dir']))
			$directory = $request['dir'];
		else
			$directory = 'app';

		if(isset($request['file']))
			$res = $request['file'];
		else
			$res = 'tests/AutoTest.php';

		copy('sample.php.txt', $res);

		$routes = \Router::getRoutes();
		$routes = \BundlesManager::getRoutesFromDirectory($directory);
		usort($routes, function($a, $b) {
			return $a['route'] > $b['route'];
		});

		foreach($routes as $route=>$params) {
			$method = strtolower($params['method']);
			if(!$method)
				$method = 'get';

			#get
			if($method === 'get' || $method === 'delete') {
				if(strpos($params['route'], ':') !== false) {
					// continue;
					#get params
					file_put_contents($res, '
				/*
				$browser = new Browser;
				$this->assertEquals(200, $browser->'.$method.'(\''.$params['route'].'\')->getCode(), \''.strtoupper($method).' '.$params['route'].'\');
				*/
				', FILE_APPEND);
				}
				else {
				file_put_contents($res, '
				$browser = new Browser;
				$this->assertEquals(200, $browser->'.$method.'(\''.$params['route'].'\')->getCode(), \''.strtoupper($method).' '.$params['route'].'\');
				', FILE_APPEND);
				}
			}
			else {
				// continue;
				#post params
				file_put_contents($res, '
				/*
				$browser = new Browser;
				$this->assertEquals(200, $browser->'.$method.'(\''.strtoupper($method).' '.$params['route'].'\',
					array(),
					array(),
				)->getCode(), \''.$params['route'].'\');
				*/
				', FILE_APPEND);
			}
		}

		file_put_contents($res, '
			}
		}
		', FILE_APPEND);
	}
}