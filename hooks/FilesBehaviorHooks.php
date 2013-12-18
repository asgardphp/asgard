<?php
namespace Coxis\Files\Hooks;

class FilesBehaviorHooks extends \Coxis\Hook\HooksContainer {
	/**
	@Hook('behaviors_pre_load')
	**/
	public static function behaviors_pre_load($chain, $entityDefinition) {
		if(!isset($entityDefinition->behaviors['Coxis\Files\FilesBehavior']))
			$entityDefinition->behaviors['Coxis\Files\FilesBehavior'] = true;
	}
}