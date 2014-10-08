<?php
namespace Asgard\Orm\Tests;

class UniqueTest extends \PHPUnit_Framework_TestCase {
	public function test1() {
		#Dependencies
		$container = new \Asgard\Container\Container;
		$em = new \Asgard\Entity\EntitiesManager;
		$rulesRegistry = new \Asgard\Validation\RulesRegistry;
		$rulesRegistry->register('unique', 'Asgard\Orm\Rules\Unique');
		$em->setValidatorFactory(new \Asgard\Validation\ValidatorFactory($rulesRegistry));
		$db = new \Asgard\Db\DB([
			'host'     => 'localhost',
			'user'     => 'root',
			'password' => '',
			'database' => 'asgard'
		]);
		$dataMapper = new \Asgard\Orm\DataMapper($db, $em);

		#Create table for entity
		$schema = new \Asgard\Db\Schema($db);
		$schema->drop('test');
		(new \Asgard\Orm\ORMMigrations($dataMapper))->autoMigrate([
			$em->get('Asgard\Orm\Tests\Fixtures\Unique\Test')
		], $schema);

		#Fixtures
		$dataMapper->create('Asgard\Orm\Tests\Fixtures\Unique\Test', ['name'=>'not unique name']);

		#Text saving an entity
		$test = $em->make('Asgard\Orm\Tests\Fixtures\Unique\Test');

		$test->name = 'unique name';
		$this->assertEquals(
			[],
			$dataMapper->errors($test)
		);

		$test->name = 'not unique name';
		$this->assertEquals([
				'name' => ['unique' => 'Name must be unique.']
			],
			$dataMapper->errors($test)
		);
	}
}