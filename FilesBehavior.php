<?php
namespace Coxis\Files;

class FilesBehavior implements \Coxis\Core\Behavior {
	public static function load($entityDefinition, $params=null) {
		$entityName = $entityDefinition->getClass();

		static::loadValidationRules();
	
		$entityDefinition->hookBefore('propertyClass', function($chain, $type) {
			if($type == 'file')
				return '\Coxis\Files\Libs\FileProperty';
		});

		#$article->hasFile('image')
		$entityDefinition->addMethod('hasFile', function($entity, $file) {
			return $entity::hasProperty($file) && $entity::property($file)->type == 'file';
		});
		#Article::files()
		$entityDefinition->addStaticMethod('files', function($name, $args) use($entityName) {
			$res = array();
			foreach($entityName::properties() as $name=>$property)
				if($property->type == 'file')
					$res[$name] = $property;
			return $res;
		});

		$entityDefinition->hookBefore('save', function($chain, $entity) {
			foreach($entity::properties() as $name=>$property)
				if($property->type == 'file')
					$entity->$name->save();
		});

		$entityDefinition->hookOn('destroy', function($chain, $entity) {
			foreach($entity::properties() as $name=>$property)
				if($property->type == 'file')
					$entity->$name->delete();
		});
	}
	
	public static function loadValidationRules() {
		if(!\Validation::ruleExists('filerequired')) {
			\Validation::register('filerequired', function($attribute, $value, $params, $validator) {
				if(!$params[0])
					return;
				$msg = false;
				if(!$value)
					$msg = $validator->getMessage('filerequired', $attribute, __('The file ":attribute" is required.'));
				elseif(!$value->exists())
					$msg = $validator->getMessage('fileexists', $attribute, __('The file ":attribute" does not exist.'));
				if($msg) {
					return \Validation::format($msg, array(
						'attribute'	=>	$attribute,
					));
				}
			});
		}
		
		if(!\Validation::ruleExists('image')) {
			\Validation::register('image', function($attribute, $value, $params, $validator) {
				try {
					$mime = mime_content_type($value['tmp_name']);
					if(!in_array($mime, array('image/jpeg', 'image/png', 'image/gif'))) {
						$msg = $validator->getMessage('image', $attribute, __('The file ":attribute" must be an image.'));
						return \Validation::format($msg, array(
							'attribute'	=>	$attribute,
						));
					}
				} catch(\ErrorException $e) {}
			});
		}

		if(!\Validation::ruleExists('allowed')) {
			\Validation::register('allowed', function($attribute, $value, $params, $validator) {
				if($ext = $value->notAllowed()) {
					$msg = $validator->getMessage('image', $attribute, __('This type of file is not allowed ":ext".'));
					return \Validation::format($msg, array(
						'attribute'	=>	$attribute,
						'ext'	=>	$ext,
					));
				}
			});
		}
	}
}