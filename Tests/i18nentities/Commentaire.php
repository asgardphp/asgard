<?php
namespace Asgard\Orm\Tests\I18Nentities;

class Commentaire extends \Asgard\Core\Entity {
	public static $properties = array(
		'titre',
	);

	public static $behaviors = array(
		'Asgard\Orm\ORMBehavior'
	);
	
	public static $relations = array(
		'actualite'	=>	array(
			'entity'	=>	'\Asgard\Orm\Tests\I18Nentities\Actualite',
			'has'	=>	'one',
		),
	);
}