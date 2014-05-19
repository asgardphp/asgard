<?php
namespace Asgard\Orm\Tests\I18nentities;

class Actualite extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = array(
			'titre',
			'date'    =>    array(
				'validation' => array(
					'required'	=>	false,
				)
			),
			'lieu'    =>    array(
				'validation' => array(
					'required'	=>	false,
				)
			),
			'introduction',
			'contenu' => array(
				'validation' => array(
					'required'	=>	true,
				)
			),
			'test'	=>	array(
				'i18n'	=>	true,
				'validation' => array(
					'required'	=>	false,
				)
			),
		);

		$definition->behaviors = array(
			new \Asgard\Orm\ORMBehavior
		);

		$definition->relations = array(
			'commentaires'	=>	array(
				'entity'	=>	'\Asgard\Orm\Tests\I18Nentities\Commentaire',
				'has'		=>	'many',
			),
		);
	}
}