<?php
namespace Asgard\Orm\Hooks;

class ORMBehaviorHooks extends \Asgard\Hook\HooksContainer {
	/**
	@Hook('behaviors_pre_load')
	**/
	public static function behaviors_pre_load($chain, $entityDefinition) {
		if(!isset($entityDefinition->behaviors['orm']))
			$entityDefinition->behaviors['Asgard\Orm\ORMBehavior'] = true;
	}
}