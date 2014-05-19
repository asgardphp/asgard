<?php
namespace Asgard\Orm\Tests\I18nentities;

class Commentaire extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = array(
			'titre',
		);

		$definition->behaviors = array(
			new \Asgard\Orm\ORMBehavior
		);

		$definition->relations = array(
			'actualite'	=>	array(
				'entity'	=>	'\Asgard\Orm\Tests\I18Nentities\Actualite',
				'has'	=>	'one',
			),
		);
	}
}