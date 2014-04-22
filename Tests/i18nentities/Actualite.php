<?php
namespace Asgard\Orm\Tests\I18Nentities;

class Actualite extends \Asgard\Core\Entity {
	public static $properties = array(
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

	public static $behaviors = array(
		'Asgard\Orm\ORMBehavior'
	);
	
	public static $relations = array(
		'commentaires'	=>	array(
			'entity'	=>	'\Asgard\Orm\Tests\I18Nentities\Commentaire',
			'has'		=>	'many',
		),
	);
}