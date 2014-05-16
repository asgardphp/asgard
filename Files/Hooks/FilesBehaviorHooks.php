<?php
namespace Asgard\Files\Hooks;

class FilesBehaviorHooks extends \Asgard\Hook\HooksContainer {
	/**
	@Hook('behaviors_pre_load')
	**/
	public static function behaviors_pre_load($chain, $definition) {
		$definition->addBehavior(new \Asgard\Files\FilesBehavior);
	}

	/**
	@Hook('entity_property_type')
	**/
	public static function entity_property_type($chain, $type) {
		if($type == 'file')
			return '\Asgard\Files\Libs\FileProperty';
	}
}