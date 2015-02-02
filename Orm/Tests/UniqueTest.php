<?php
namespace Asgard\Orm\Tests;

class UniqueTest extends \PHPUnit_Framework_TestCase {
	public function test1() {
		#Dependencies
		$em = new \Asgard\Entity\EntityManager;
		$rulesRegistry = new \Asgard\Validation\RulesRegistry;
		$rulesRegistry->register('unique', 'Asgard\Orm\Rules\Unique');
		$em->setValidatorFactory(new \Asgard\Validation\ValidatorFactory($rulesRegistry));
		$db = new \Asgard\Db\DB([
			'driver' => 'sqlite',
			'database' => ':memory:',
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