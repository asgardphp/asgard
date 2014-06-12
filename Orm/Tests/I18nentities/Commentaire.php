<?php
namespace Asgard\Orm\Tests\I18nentities;

class Commentaire extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'titre',
		];

		$definition->behaviors = [
			new \Asgard\Orm\ORMBehavior
		];

		$definition->relations = [
			'actualite'	=>	[
				'entity'	=>	'\Asgard\Orm\Tests\I18nentities\Actualite',
				'has'	=>	'one',
			],
		];
	}
}