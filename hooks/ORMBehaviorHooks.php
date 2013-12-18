<?php
namespace Coxis\ORM\Hooks;

class ORMBehaviorHooks extends \Coxis\Hook\HooksContainer {
	/**
	@Hook('behaviors_pre_load')
	**/
	public static function behaviors_pre_load($chain, $entityDefinition) {
		if(!isset($entityDefinition->behaviors['orm']))
			$entityDefinition->behaviors['Coxis\ORM\ORMBehavior'] = true;
	}
}