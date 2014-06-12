<?php
namespace Asgard\Orm\Tests\I18nentities;

class Actualite extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'titre',
			'date'    =>    [
				'validation' => [
					'required'	=>	false,
				]
			],
			'lieu'    =>    [
				'validation' => [
					'required'	=>	false,
				]
			],
			'introduction',
			'contenu' => [
				'validation' => [
					'required'	=>	true,
				]
			],
			'test'	=>	[
				'i18n'	=>	true,
				'validation' => [
					'required'	=>	false,
				]
			],
		];

		$definition->behaviors = [
			new \Asgard\Orm\ORMBehavior
		];

		$definition->relations = [
			'commentaires'	=>	[
				'entity'	=>	'\Asgard\Orm\Tests\I18nentities\Commentaire',
				'has'		=>	'many',
			],
		];
	}
}