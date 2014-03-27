<?php
namespace Asgard\Files;

class FilesBehavior implements \Asgard\Core\Behavior {
	public static function load($entityDefinition, $params=null) {
		$entityName = $entityDefinition->getClass();

		// static::loadValidationRules();
		// d(\Asgard\Core\App::instance());
		\Asgard\Core\App::get('rulesregistry')->registerNamespace('Asgard\Files\Rules');
	
		$entityDefinition->hookBefore('propertyClass', function($chain, $type) {
			if($type == 'file')
				return '\Asgard\Files\Libs\FileProperty';
		});

		#$article->hasFile('image')
		$entityDefinition->addMethod('hasFile', function($entity, $file) {
			return $entity::hasProperty($file) && $entity::property($file) instanceof Libs\FileProperty;
		});
		#Article::fileProperties()
		$entityDefinition->addStaticMethod('fileProperties', function() use($entityName) {
			$res = array();
			foreach($entityName::properties() as $name=>$property) {
				if($property instanceof Libs\FileProperty)
					$res[$name] = $property;
			}
			return $res;
		});
		#$article->files()
		$entityDefinition->addMethod('files', function($entity) {
			$res = array();
			foreach($entity->toArrayRaw() as $name=>$value) {
				if($entity->hasFile($name))
					$res[$name] = $value;
			}
			return $res;
		});

		$entityDefinition->hookBefore('save', function($chain, $entity) {
			foreach($entity->files() as $file)
				$file->save();
		});

		$entityDefinition->hookOn('destroy', function($chain, $entity) {
			foreach($entity->files() as $file)
				$file->delete();
		});
	}
	
	/*
	protected static function loadValidationRules() {
		if(!\Asgard\Core\App::get('validation')->ruleExists('filerequired')) {
			\Asgard\Core\App::get('validation')->register('filerequired', function($attribute, $value, $params, $validator) {
				if(!$params[0])
					return;
				$msg = false;
				if(!$value)
					$msg = $validator->getMessage('filerequired', $attribute, __('The file ":attribute" is required.'));
				elseif(!$value->exists())
					$msg = $validator->getMessage('fileexists', $attribute, __('The file ":attribute" does not exist.'));
				if($msg) {
					return \Asgard\Core\App::get('validation')->format($msg, array(
						'attribute'	=>	$attribute,
					));
				}
			});
		}
		
		if(!\Asgard\Core\App::get('validation')->ruleExists('image')) {
			\Asgard\Core\App::get('validation')->register('image', function($attribute, $value, $params, $validator) {
				if(!$value->exists())
					return;

	            $finfo = \finfo_open(FILEINFO_MIME);
	            $mime = \finfo_file($finfo, $value->get(null, true));
	            \finfo_close($finfo);
	            list($mime) = explode(';', $mime);

				if(!in_array($mime, array('image/jpeg', 'image/png', 'image/gif'))) {
					$msg = $validator->getMessage('image', $attribute, __('The file ":attribute" must be an image.'));
					return \Asgard\Validation\Validation::format($msg, array(
						'attribute'	=>	$attribute,
					));
				}
			});
		}

		if(!\Asgard\Core\App::get('validation')->ruleExists('allowed')) {
			\Asgard\Core\App::get('validation')->register('allowed', function($attribute, $value, $params, $validator) {
				if(!$value->exists())
					return;

				if(!in_array($value->extension(), $params[0])) {
					$msg = $validator->getMessage('image', $attribute, __('This type of file is not allowed ":ext".'));
					return \Asgard\Core\App::get('validation')->format($msg, array(
						'attribute'	=>	$attribute,
						'ext'	=>	$value->extension(),
					));
				}
			});
		}
	}
	*/
}