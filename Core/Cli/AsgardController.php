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
		$browser = $this->getBrowser();
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
					$bundle['entities'][$name]['front'] = false;
				if(!is_array($bundle['entities'][$name]['front'])) 
					$bundle['entities'][$name]['front'] = array('index', 'show');
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
					if(!isset($bundle['controllers'][$name]['actions'][$aname]['viewFile']))
						$bundle['controllers'][$name]['actions'][$aname]['viewFile'] = null;
				}
			}

			if(!isset($bundle['tests']))
				$bundle['tests'] = false;

			$bundles[$bundle_name] = $bundle;
		}


		foreach($bundles as $name=>$bundle) {
			if($bundle['tests']) {
				$generatedTests = '';
				$tests = array();
			}

			$dst = 'app/'.strtolower($name).'/';
			static::processFile(__DIR__.'/bundle_template/Bundle.php', $dst.'Bundle.php', array('bundle'=>$bundle));
			foreach($bundle['entities'] as $name=>$entity) {
				static::processFile(__DIR__.'/bundle_template/entities/_Entity.php', $dst.'entities/'.ucfirst($bundle['entities'][$name]['meta']['name']).'.php', array('bundle'=>$bundle, 'entity'=>$entity));
				if($entity['front']) {
					static::processFile(__DIR__.'/bundle_template/controllers/_EntityController.php', $dst.'controllers/'.ucfirst($bundle['entities'][$name]['meta']['name']).'Controller.php', array('bundle'=>$bundle, 'entity'=>$entity));

					if($bundle['tests']) {
						include_once $dst.'controllers/'.ucfirst($bundle['entities'][$name]['meta']['name']).'Controller.php';
						$class = $bundle['namespace'].'\\Controllers\\'.ucfirst($entity['meta']['name']).'Controller';
					}

					if(in_array('index', $entity['front']) || isset($entity['front']['index'])) {
						if(isset($entity['front']['index']))
							\Asgard\Utils\FileManager::copy($entity['front']['index'], _DIR_.$dst.'views/'.$bundle['entities'][$name]['meta']['name'].'/index.php', false);
						else
							static::processFile(__DIR__.'/bundle_template/views/_entity/index.php', _DIR_.$dst.'views/'.$bundle['entities'][$name]['meta']['name'].'/index.php', array('bundle'=>$bundle, 'entity'=>$entity));
						if($bundle['tests']) {
							$indexRoute = $class::route_for('index');
							$tests[$indexRoute] = '
		$browser = $this->getBrowser();
		$this->assertTrue($browser->get(\''.$indexRoute.'\')->isOK(), \'GET '.$indexRoute.'\');';
						}
					}
					if(in_array('show', $entity['front']) || isset($entity['front']['show'])) {
						if(isset($entity['front']['show']))
							\Asgard\Utils\FileManager::copy($entity['front']['show'], _DIR_.$dst.'views/'.$bundle['entities'][$name]['meta']['name'].'/show.php', false);
						else
							static::processFile(__DIR__.'/bundle_template/views/_entity/show.php', _DIR_.$dst.'views/'.$bundle['entities'][$name]['meta']['name'].'/show.php', array('bundle'=>$bundle, 'entity'=>$entity));
						if($bundle['tests']) {
							$showRoute = $class::route_for('show');
							$tests[$showRoute] = '
		$browser = $this->getBrowser();
		$this->assertTrue($browser->get(\''.$showRoute.'\')->isOK(), \'GET '.$showRoute.'\');';
						}
					}
				}
			}

			foreach($bundle['controllers'] as $name=>$controller) {
				static::processFile(__DIR__.'/bundle_template/controllers/_Controller.php', $dst.'controllers/'.$controller['name'].'.php', array('bundle'=>$bundle, 'controller'=>$controller));

				if($bundle['tests']) {
					include_once $dst.'controllers/'.$controller['name'].'.php';
					$class = $bundle['namespace'].'\\Controllers\\'.ucfirst($controller['name']);
				}

				foreach($controller['actions'] as $action=>$params) {
					if($bundle['tests']) {
						$actionRoute = $class::route_for($action);
						if(!$actionRoute)
							continue;
						$tests[$actionRoute] = '
		$browser = $this->getBrowser();
		$this->assertTrue($browser->get(\''.$actionRoute.'\')->isOK(), \'GET '.$actionRoute.'\');';
					}
					if($params['template']) {
						$content = '';
						if($params['viewFile'])
							$content = file_get_contents($params['viewFile']);
						\Asgard\Utils\FileManager::put($dst.'views/'.strtolower(preg_replace('/Controller$/', '', $controller['name'])).'/'.$params['template'], $content);
					}
				}
			}

			if($bundle['tests'])
				$bundle['generatedTests'] = $tests;

			\Asgard\Core\App::get('hook')->trigger('Agard\CLI\generator\bundleBuild', array(&$bundle, 'app/'.strtolower($bundle['name']).'/'));

			if($bundle['tests'])
				$this->addToTests($bundle['generatedTests'], ucfirst($bundle['name']));
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

	protected function createAutotestFile($dst=null) {
		if(!$dst)
			$dst = _DIR_.'tests/AutoTest.php';
		if(!file_exists($dst))
			copy(__DIR__.'/sample.php.txt', $dst);
	}

	protected function addToTests($tests, $dst) {
		$res = '';
		foreach($tests as $route=>$test) {
			$test = trim($test);
			if(strpos($route, ':') !== false)
				$test = "/*\n\t\t".$test."\n"."\t\t*/";
			$res .= "\t\t".$test."\n\n";
		}

		if(file_exists(_DIR_.'tests/'.$dst.'.php')) {
			echo 'Test '.$dst.' already exists.';
			return;
		}
		file_put_contents(_DIR_.'tests/'.$dst.'.php', '<?php
namespace App\Tests;

class '.$dst.' extends \Asgard\Core\Test {
	public function test1() {
		'.trim($res).'
	}
}');
	}

	protected function addToAutotest($tests, $dst=null) {
		$res = '';
		foreach($tests as $route=>$test) {
			$test = trim($test);
			if(strpos($route, ':') !== false)
				$test = "/*\n\t\t".$test."\n"."\t\t*/";
			$res .= "\t\t".$test."\n\n";
		}

		if(!$dst)
			$dst = _DIR_.'tests/AutoTest.php';
		$this->createAutotestFile($dst);
		$original = file_get_contents($dst);
		$res = str_replace('/* Autotest - DO NOT MODIFY THIS LINE */', '/* Autotest - DO NOT MODIFY THIS LINE */'."\n".$res, $original);
		file_put_contents($dst, $res);
	}

	public function generateTestsAction($request) {
		\Asgard\Utils\FileManager::unlink(_DIR_.'tests/tested.txt');

		exec('phpunit --bootstrap '._DIR_.'tests/bootstrap.php '._DIR_.'tests', $res);

		if(strpos(implode("\n", $res), 'FAILURES!') !== false) {
			echo 'Tests failed.';
			return;
		}

		if(!file_exists(_DIR_.'tests/tested.txt')) {
			echo 'Tests failed.';
			return;
		}

		$tested = array_filter(explode("\n", file_get_contents(_DIR_.'tests/tested.txt')));
		if(file_exists(_DIR_.'tests/ignore.txt'))
			$tested = array_merge(array_filter(explode("\n", file_get_contents(_DIR_.'tests/ignore.txt'))));
		\Asgard\Utils\FileManager::unlink(_DIR_.'tests/tested.txt');

		if(isset($request['dir']))
			$directory = $request['dir'];
		else
			$directory = 'app';

		if(isset($request['file']))
			$dst = $request['file'];
		else
			$dst = null;

		$routes = \Asgard\Core\App::get('resolver')->getRoutes();

		$res = array();


		foreach($routes as $route) {
			// $class = $route->getController();
			// d($route);
		// $reflection = new \Addendum\ReflectionAnnotatedClass($route->getController());
		// $mreflection = $reflection->getMethod($route->getAction().'Action');
		// if($annotations->getAnnotation('Test'))
		// 	d($annotations->getAnnotation('Test')->value);
			// d($route);
			// $function = $route->getAction().'Action';

			$annotations = new \Addendum\ReflectionAnnotatedMethod($route->getController(), $route->getAction().'Action');
		// d(123);
			if($annotations->getAnnotation('Test') && $annotations->getAnnotation('Test')->value === false)
				continue;

			foreach($tested as $url) {
				if(\Asgard\Core\App::get('resolver')->matchWith($route->getRoute(), $url) !== false)
					continue 2;
			}

			$method = strtolower($route->get('method'));
			if(!$method)
				$method = 'get';

			#get
			if($method === 'get' || $method === 'delete') {
				if(strpos($route->getRoute(), ':') !== false) {
					#get params
					$res[] = '
		/*
		$browser = $this->getBrowser();
		$this->assertTrue($browser->'.$method.'(\''.$route->getRoute().'\')->isOK(), \''.strtoupper($method).' '.$route->getRoute().'\');
		*/
		';
				}
				else {
				$res[] = '
		$browser = $this->getBrowser();
		$this->assertTrue($browser->'.$method.'(\''.$route->getRoute().'\')->isOK(), \''.strtoupper($method).' '.$route->getRoute().'\');
		';
				}
			}
			else {
				#post params
				$res[] = '
		/*
		$browser = $this->getBrowser();
		$this->assertTrue($browser->'.$method.'(\''.strtoupper($method).' '.$route->getRoute().'\',
			array(),
			array(),
		)->isOK(), \''.$route->getRoute().'\');
		*/
		';
			}
		}

		$this->addToAutotest($res, $dst);
	}
}