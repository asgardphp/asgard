<?php
namespace Asgard\Files\Hooks;

class FilesBehaviorHooks extends \Asgard\Hook\HooksContainer {
	/**
	@Hook('behaviors_pre_load')
	**/
	public static function behaviors_pre_load($chain, $entityDefinition) {
		if(!isset($entityDefinition->behaviors['Asgard\Files\FilesBehavior']))
			$entityDefinition->behaviors['Asgard\Files\FilesBehavior'] = true;
	}
}