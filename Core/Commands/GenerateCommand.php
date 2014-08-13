<?php
namespace Asgard\Core\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GenerateCommand extends \Asgard\Console\Command {
	protected $name = 'generate';
	protected $description = 'Generate bundles from a single YAML file';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$asgard = $this->getContainer();
		$path = $this->input->getArgument('path');
		$root = $asgard['kernel']['root'].'/';
	
		$yaml = new \Symfony\Component\Yaml\Parser();
		$raw = $yaml->parse(file_get_contents($path));
		$bundles = [];

		$overrideFiles = $this->input->getOption('override-bundles');
		$generator = new Generator($asgard);
		$generator->setOverrideFiles($overrideFiles);
		
		foreach($raw as $bundle_name=>$bundle) {
			if(file_exists($root.'app/'.$bundle_name.'/')) {
				if($this->input->getOption('override-bundles'))
					\Asgard\File\FileSystem::delete($root.'app/'.$bundle_name.'/');
				elseif($this->input->getOption('skip'))
					continue;
			}
			
			$bundle['name'] = strtolower($bundle_name);
			$bundle['namespace'] = ucfirst($bundle['name']);
			
			if(!isset($bundle['entities']))
				$bundle['entities'] = [];
			if(!isset($bundle['controllers']))
				$bundle['controllers'] = [];

			foreach($bundle['entities'] as $name=>$entity) {
				if(!isset($bundle['entities'][$name]['meta']))
					$bundle['entities'][$name]['meta'] = [];
				if(isset($bundle['entities'][$name]['meta']['name']))
					$bundle['entities'][$name]['meta']['name'] = strtolower($bundle['entities'][$name]['meta']['name']);
				else
					$bundle['entities'][$name]['meta']['name'] = strtolower($name);

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
				elseif(isset($bundle['entities'][$name]['meta']['plural']))
					$bundle['entities'][$name]['meta']['label_plural'] = strtolower($bundle['entities'][$name]['meta']['plural']);
				else
					$bundle['entities'][$name]['meta']['label_plural'] = $bundle['entities'][$name]['meta']['label'].'s';
				if(!isset($bundle['entities'][$name]['meta']['name_field'])) {
					$properties = array_keys($bundle['entities'][$name]['properties']);
					$bundle['entities'][$name]['meta']['name_field'] = $properties[0];
				}
					
				if(!isset($bundle['entities'][$name]['properties']))
					$bundle['entities'][$name]['properties'] = [];
				if(!isset($bundle['entities'][$name]['relations']))
					$bundle['entities'][$name]['relations'] = [];
				if(!isset($bundle['entities'][$name]['behaviors']))
					$bundle['entities'][$name]['behaviors'] = [];

				foreach($bundle['entities'][$name]['properties'] as $k=>$v) {
					if(!$v)
						$bundle['entities'][$name]['properties'][$k] = [];
					if(!is_array($v))
						$bundle['entities'][$name]['properties'][$k] = ['type'=>$v];
				}

				if(!isset($bundle['entities'][$name]['front']))
					$bundle['entities'][$name]['front'] = false;
				if($bundle['entities'][$name]['front'] && !is_array($bundle['entities'][$name]['front'])) 
					$bundle['entities'][$name]['front'] = ['index', 'show'];
			}

			foreach($bundle['controllers'] as $name=>$controller) {
				$bundle['controllers'][$name]['name'] = $name;
				if(!isset($bundle['controllers'][$name]['prefix']))
					$bundle['controllers'][$name]['prefix'] = null;
				if(!isset($bundle['controllers'][$name]['actions']))
					$bundle['controllers'][$name]['actions'] = [];
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
			if($bundle['tests'])
				$tests = [];

			$dst = $root.'app/'.ucfirst(strtolower($name)).'/';
			$generator->processFile(__DIR__.'/bundle_template/Bundle.php', $dst.'Bundle.php', ['bundle'=>$bundle]);
			foreach($bundle['entities'] as $name=>$entity) {
				$generator->processFile(__DIR__.'/bundle_template/Entities/_Entity.php', $dst.'Entities/'.ucfirst($bundle['entities'][$name]['meta']['name']).'.php', ['bundle'=>$bundle, 'entity'=>$entity]);
				if($entity['front']) {
					$generator->processFile(__DIR__.'/bundle_template/Controllers/_EntityController.php', $dst.'Controllers/'.ucfirst($bundle['entities'][$name]['meta']['name']).'Controller.php', ['bundle'=>$bundle, 'entity'=>$entity]);

					if($bundle['tests']) {
						include_once $dst.'Controllers/'.ucfirst($bundle['entities'][$name]['meta']['name']).'Controller.php';
						$class = $bundle['namespace'].'\\Controllers\\'.ucfirst($entity['meta']['name']).'Controller';
					}

					if(in_array('index', $entity['front']) || isset($entity['front']['index'])) {
						if(isset($entity['front']['index']))
							\Asgard\File\FileSystem::copy($entity['front']['index'], $dst.'html/'.strtolower($bundle['entities'][$name]['meta']['name'].'/index.php'), false);
						else
							$generator->processFile(__DIR__.'/bundle_template/html/_entity/index.php', $dst.'html/'.strtolower($bundle['entities'][$name]['meta']['name'].'/index.php'), ['bundle'=>$bundle, 'entity'=>$entity]);
						if($bundle['tests']) {
							$indexRoute = $class::routeFor('index')->getRoute();
							$tests[$indexRoute] = '
		$browser = $this->getBrowser();
		$this->assertTrue($browser->get(\''.$indexRoute.'\')->isOK(), \'GET '.$indexRoute.'\');';
						}
					}
					if(in_array('show', $entity['front']) || isset($entity['front']['show'])) {
						if(isset($entity['front']['show']))
							\Asgard\File\FileSystem::copy($entity['front']['show'], $dst.'html/'.strtolower($bundle['entities'][$name]['meta']['name'].'/show.php'), false);
						else
							$generator->processFile(__DIR__.'/bundle_template/html/_entity/show.php', $dst.'html/'.strtolower($bundle['entities'][$name]['meta']['name'].'/show.php'), ['bundle'=>$bundle, 'entity'=>$entity]);
						if($bundle['tests']) {
							$showRoute = $class::routeFor('show')->getRoute();
							$tests[$showRoute] = '
		$browser = $this->getBrowser();
		$this->assertTrue($browser->get(\''.$showRoute.'\')->isOK(), \'GET '.$showRoute.'\');';
						}
					}
				}
			}

			foreach($bundle['controllers'] as $name=>$controller) {
				$generator->processFile(__DIR__.'/bundle_template/Controllers/_Controller.php', $dst.'Controllers/'.$controller['name'].'.php', ['bundle'=>$bundle, 'controller'=>$controller]);

				if($bundle['tests']) {
					include_once $dst.'Controllers/'.$controller['name'].'.php';
					$class = $bundle['namespace'].'\\Controllers\\'.ucfirst($controller['name']);
				}

				foreach($controller['actions'] as $action=>$params) {
					if($bundle['tests']) {
						$actionRoute = $class::routeFor($action);
						if(!$actionRoute)
							continue;
						else
							$actionRoute = $actionRoute->getRoute();
						$tests[$actionRoute] = '
		$browser = $this->getBrowser();
		$this->assertTrue($browser->get(\''.$actionRoute.'\')->isOK(), \'GET '.$actionRoute.'\');';
					}
					if($params['template']) {
						$content = '';
						if($params['viewFile'])
							$content = file_get_contents($params['viewFile']);
						\Asgard\File\FileSystem::write($dst.'html/'.strtolower(preg_replace('/Controller$/', '', $controller['name'])).'/'.$params['template'], $content);
					}
				}
			}

			if($bundle['tests'])
				$bundle['generatedTests'] = $tests;

			$asgard['hooks']->trigger('Asgard.Core.Generate.bundleBuild', [&$bundle, $root.'app/'.ucfirst($bundle['name']).'/', $generator]);

			if($bundle['tests']) {
				if(!$this->addToTests($bundle['generatedTests'], $root.'tests/'.ucfirst($bundle['name']).'Test.php'))
					$this->comment($root.'tests/'.ucfirst($bundle['name']).'Test.php could not be generated.');
			}
		}
			

		$this->info('Bundles created: '.implode(', ', array_keys($bundles)));
	}

	protected function addToTests($tests, $dst) {
		if(!$tests)
			return true;

		$res = '';
		foreach($tests as $route=>$test) {
			$test = trim($test);
			if(strpos($route, ':') !== false)
				$test = "/*\n\t\t".$test."\n"."\t\t*/";
			$res .= "\t\t".$test."\n\n";
		}

		if(file_exists($dst))
			return false;

		file_put_contents($dst, '<?php
class '.basename($dst, '.php').' extends \Asgard\Http\Test {
	public function test1() {
		'.trim($res).'
	}
}');

		return true;
	}

	protected function getArguments() {
		return [
			['path', InputArgument::REQUIRED, 'Path to the YAML file']
		];
	}

	protected function getOptions() {
		return [
			['override-bundles', null, InputOption::VALUE_NONE, 'Override existing bundles', null],
			['override-files', null, InputOption::VALUE_NONE, 'Override existing files', null],
			['skip', null, InputOption::VALUE_NONE, 'Skip existing bundles', null],
		];
	}
}