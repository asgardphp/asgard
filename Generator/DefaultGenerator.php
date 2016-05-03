<?php
namespace Asgard\Generator;

class DefaultGenerator extends AbstractGenerator {
	public function preGenerate(array &$bundle) {
		$bundle['namespace'] = $bundle['name'];

		if(!isset($bundle['entities']))
			$bundle['entities'] = [];
		if(!isset($bundle['controllers']))
			$bundle['controllers'] = [];

		foreach($bundle['entities'] as $entityName=>$entity) {
			if(!isset($bundle['entities'][$entityName]['meta']))
				$bundle['entities'][$entityName]['meta'] = [];
			if(isset($bundle['entities'][$entityName]['meta']['name']))
				$bundle['entities'][$entityName]['meta']['name'] = strtolower($bundle['entities'][$entityName]['meta']['name']);
			else
				$bundle['entities'][$entityName]['meta']['name'] = strtolower($entityName);

			$bundle['entities'][$entityName]['meta']['entityClass'] = $bundle['namespace'].'\Entity\\'.ucfirst($entityName);

			if(isset($bundle['entities'][$entityName]['meta']['plural']))
				$bundle['entities'][$entityName]['meta']['plural'] = strtolower($bundle['entities'][$entityName]['meta']['plural']);
			else
				$bundle['entities'][$entityName]['meta']['plural'] = $bundle['entities'][$entityName]['meta']['name'].'s';
			if(isset($bundle['entities'][$entityName]['meta']['label']))
				$bundle['entities'][$entityName]['meta']['label'] = strtolower($bundle['entities'][$entityName]['meta']['label']);
			else
				$bundle['entities'][$entityName]['meta']['label'] = $bundle['entities'][$entityName]['meta']['name'];
			if(isset($bundle['entities'][$entityName]['meta']['label_plural']))
				$bundle['entities'][$entityName]['meta']['label_plural'] = strtolower($bundle['entities'][$entityName]['meta']['label_plural']);
			elseif(isset($bundle['entities'][$entityName]['meta']['plural']))
				$bundle['entities'][$entityName]['meta']['label_plural'] = strtolower($bundle['entities'][$entityName]['meta']['plural']);
			else
				$bundle['entities'][$entityName]['meta']['label_plural'] = $bundle['entities'][$entityName]['meta']['label'].'s';
			if(!isset($bundle['entities'][$entityName]['meta']['name_field'])) {
				$properties = array_keys($bundle['entities'][$entityName]['properties']);
				$bundle['entities'][$entityName]['meta']['name_field'] = $properties[0];
			}

			if(!isset($bundle['entities'][$entityName]['properties']))
				$bundle['entities'][$entityName]['properties'] = [];
			if(!isset($bundle['entities'][$entityName]['relations']))
				$bundle['entities'][$entityName]['relations'] = [];
			if(!isset($bundle['entities'][$entityName]['behaviors']))
				$bundle['entities'][$entityName]['behaviors'] = [];
			if(!isset($bundle['entities'][$entityName]['metas']))
				$bundle['entities'][$entityName]['metas'] = [];

			foreach($bundle['entities'][$entityName]['properties'] as $k=>$v) {
				if(!$v)
					$bundle['entities'][$entityName]['properties'][$k] = [];
				if(!is_array($v))
					$bundle['entities'][$entityName]['properties'][$k] = ['type'=>$v];
			}

			if(!isset($bundle['entities'][$entityName]['front']))
				$bundle['entities'][$entityName]['front'] = false;
			if($bundle['entities'][$entityName]['front'] && !is_array($bundle['entities'][$entityName]['front']))
				$bundle['entities'][$entityName]['front'] = ['index', 'show'];
		}

		foreach($bundle['controllers'] as $entityName=>$controller) {
			$bundle['controllers'][$entityName]['name'] = $entityName;
			if(!isset($bundle['controllers'][$entityName]['prefix']))
				$bundle['controllers'][$entityName]['prefix'] = null;
			if(!isset($bundle['controllers'][$entityName]['actions']))
				$bundle['controllers'][$entityName]['actions'] = [];
			foreach($bundle['controllers'][$entityName]['actions'] as $aname=>$action) {
				if(!isset($bundle['controllers'][$entityName]['actions'][$aname]['template']))
					$bundle['controllers'][$entityName]['actions'][$aname]['template'] = null;
				if(!isset($bundle['controllers'][$entityName]['actions'][$aname]['route']))
					$bundle['controllers'][$entityName]['actions'][$aname]['route'] = null;
				if(!isset($bundle['controllers'][$entityName]['actions'][$aname]['viewFile']))
					$bundle['controllers'][$entityName]['actions'][$aname]['viewFile'] = null;
			}
		}
	}

	public function generate(array $bundle, $root, $bundlePath) {
		$this->engine->processFile(__DIR__.'/bundle_template/Bundle.php', $bundlePath.'Bundle.php', ['bundle'=>$bundle]);

		#entities
		foreach($bundle['entities'] as $entityName=>$entity) {
			#entity
			$this->engine->processFile(__DIR__.'/bundle_template/Entity/_Entity.php', $bundlePath.'Entity/'.ucfirst($bundle['entities'][$entityName]['meta']['name']).'.php', ['bundle'=>$bundle, 'entity'=>$entity]);

			#entity front controller
			if($entity['front']) {
				$this->engine->processFile(__DIR__.'/bundle_template/Controller/_EntityController.php', $bundlePath.'Controller/'.ucfirst($bundle['entities'][$entityName]['meta']['name']).'.php', ['bundle'=>$bundle, 'entity'=>$entity]);

				#entity index action
				if(in_array('index', $entity['front']) || isset($entity['front']['index'])) {
					if(isset($entity['front']['index']))
						\Asgard\File\FileSystem::copy($entity['front']['index'], $bundlePath.'html/'.strtolower($bundle['entities'][$entityName]['meta']['name'].'/index.php'));
					else
						$this->engine->processFile(__DIR__.'/bundle_template/html/_entity/index.php', $bundlePath.'html/'.strtolower($bundle['entities'][$entityName]['meta']['name'].'/index.php'), ['bundle'=>$bundle, 'entity'=>$entity]);
				}
				#entity show action
				if(in_array('show', $entity['front']) || isset($entity['front']['show'])) {
					if(isset($entity['front']['show']))
						\Asgard\File\FileSystem::copy($entity['front']['show'], $bundlePath.'html/'.strtolower($bundle['entities'][$entityName]['meta']['name'].'/show.php'));
					else
						$this->engine->processFile(__DIR__.'/bundle_template/html/_entity/show.php', $bundlePath.'html/'.strtolower($bundle['entities'][$entityName]['meta']['name'].'/show.php'), ['bundle'=>$bundle, 'entity'=>$entity]);
				}
			}
		}

		#controllers
		foreach($bundle['controllers'] as $controllerName=>$controller) {
			$this->engine->processFile(__DIR__.'/bundle_template/Controller/_Controller.php', $bundlePath.'Controller/'.$controller['name'].'.php', ['bundle'=>$bundle, 'controller'=>$controller]);

			#actions
			foreach($controller['actions'] as $action=>$params) {
				if($params['template'])
					$templateFile = $bundlePath.'html/'.strtolower(preg_replace('/Controller$/', '', $controller['name'])).'/'.$params['template'];
				else
					$templateFile = $bundlePath.'html/'.strtolower(preg_replace('/Controller$/', '', $controller['name'])).'/'.strtolower($action).'.php';

				#view
				$content = '';
				if($params['viewFile'])
					$content = file_get_contents($params['viewFile']);
				\Asgard\File\FileSystem::write($templateFile, $content);
			}
		}
	}
}