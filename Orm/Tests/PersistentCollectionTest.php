<?php
namespace Asgard\Orm\Tests;

class PersistentCollectionTest extends \PHPUnit_Framework_TestCase {
	public function testAdd() {
		$em = new \Asgard\Entity\EntityManager;
		$dataMapper = new \Asgard\Orm\DataMapper($db = new \Asgard\Db\DB([
			'driver' => 'sqlite',
			'database' => ':memory:',
		]), $em);
		(new \Asgard\Orm\ORMMigrations($dataMapper))->autoMigrate([
			$em->get('Asgard\Orm\Tests\Fixtures\PersistentCollection\A'),
			$em->get('Asgard\Orm\Tests\Fixtures\PersistentCollection\B'),
		]);

		#Fixtures
		$dataMapper->create('Asgard\Orm\Tests\Fixtures\PersistentCollection\A', [
			'id' => 1,
			'name' => 'foo',
			'b' => [
				$dataMapper->create('Asgard\Orm\Tests\Fixtures\PersistentCollection\B', [
					'id' => 1,
					'name' => 'bar',
				])
			],
		]);

		$newB = $dataMapper->create('Asgard\Orm\Tests\Fixtures\PersistentCollection\B', [
			'id' => 2,
			'name' => 'bar',
		]);

		$a = $dataMapper->load('Asgard\Orm\Tests\Fixtures\PersistentCollection\A', 1);
		$collection = $a->get('b');
		$collection->add($newB);
		$dataMapper->save($a);
		
		$this->assertEquals(2, $db->dal()->from('b')->where('a_id', 1)->count());
	}

	public function testAddNew() {
		$em = new \Asgard\Entity\EntityManager;
		$dataMapper = new \Asgard\Orm\DataMapper($db = new \Asgard\Db\DB([
			'driver' => 'sqlite',
			'database' => ':memory:',
		]), $em);
		(new \Asgard\Orm\ORMMigrations($dataMapper))->autoMigrate([
			$em->get('Asgard\Orm\Tests\Fixtures\PersistentCollection\A'),
			$em->get('Asgard\Orm\Tests\Fixtures\PersistentCollection\B'),
		]);

		#Fixtures
		$dataMapper->create('Asgard\Orm\Tests\Fixtures\PersistentCollection\A', [
			'id' => 1,
			'name' => 'foo',
			'b' => [
				$dataMapper->create('Asgard\Orm\Tests\Fixtures\PersistentCollection\B', [
					'id' => 1,
					'name' => 'bar',
				])
			],
		]);

		$newB = $em->make('Asgard\Orm\Tests\Fixtures\PersistentCollection\B', [
			'id' => 2,
			'name' => 'bar',
		]);

		$a = $dataMapper->load('Asgard\Orm\Tests\Fixtures\PersistentCollection\A', 1);
		$collection = $a->get('b');
		$collection->add($newB);
		$dataMapper->save($a);
		
		$this->assertEquals(2, $db->dal()->from('b')->where('a_id', 1)->count());
	}

	public function testRemove() {
		$em = new \Asgard\Entity\EntityManager;
		$dataMapper = new \Asgard\Orm\DataMapper($db = new \Asgard\Db\DB([
			'driver' => 'sqlite',
			'database' => ':memory:',
		]), $em);
		(new \Asgard\Orm\ORMMigrations($dataMapper))->autoMigrate([
			$em->get('Asgard\Orm\Tests\Fixtures\PersistentCollection\A'),
			$em->get('Asgard\Orm\Tests\Fixtures\PersistentCollection\B'),
		]);

		#Fixtures
		$dataMapper->create('Asgard\Orm\Tests\Fixtures\PersistentCollection\A', [
			'id' => 1,
			'name' => 'foo',
			'b' => [
				$b = $dataMapper->create('Asgard\Orm\Tests\Fixtures\PersistentCollection\B', [
					'id' => 1,
					'name' => 'bar',
				])
			],
		]);

		$a = $dataMapper->load('Asgard\Orm\Tests\Fixtures\PersistentCollection\A', 1);
		$collection = $a->get('b');
		$collection->remove($b);
		$dataMapper->save($a);
		
		$this->assertEquals(0, $db->dal()->from('b')->where('a_id', 1)->count());
	}

	public function testIteration() {
		$em = new \Asgard\Entity\EntityManager;
		$dataMapper = new \Asgard\Orm\DataMapper($db = new \Asgard\Db\DB([
			'driver' => 'sqlite',
			'database' => ':memory:',
		]), $em);
		(new \Asgard\Orm\ORMMigrations($dataMapper))->autoMigrate([
			$em->get('Asgard\Orm\Tests\Fixtures\PersistentCollection\A'),
			$em->get('Asgard\Orm\Tests\Fixtures\PersistentCollection\B'),
		]);

		#Fixtures
		$dataMapper->create('Asgard\Orm\Tests\Fixtures\PersistentCollection\A', [
			'id' => 1,
			'name' => 'foo',
			'b' => [
				$b1 = $dataMapper->create('Asgard\Orm\Tests\Fixtures\PersistentCollection\B', [
					'name' => 'bar1',
				]),
				$b2 = $dataMapper->create('Asgard\Orm\Tests\Fixtures\PersistentCollection\B', [
					'name' => 'bar2',
				]),
			],
		]);

		$a = $dataMapper->load('Asgard\Orm\Tests\Fixtures\PersistentCollection\A', 1);
		$collection = $a->get('b');

		$names = [];
		foreach($collection as $entity)
			$names[] = $entity->name;
		$this->assertEquals(['bar2', 'bar1'], $names);

		$collection->add($em->make('Asgard\Orm\Tests\Fixtures\PersistentCollection\B', ['name' => 'bar3']));
		$collection->remove($b1);
		$dataMapper->save($a);
		$names = [];
		foreach($collection as $entity)
			$names[] = $entity->name;
		$this->assertEquals(['bar3', 'bar2'], $names);
	}

	public function testArray() {
		$em = new \Asgard\Entity\EntityManager;
		$dataMapper = new \Asgard\Orm\DataMapper($db = new \Asgard\Db\DB([
			'driver' => 'sqlite',
			'database' => ':memory:',
		]), $em);
		(new \Asgard\Orm\ORMMigrations($dataMapper))->autoMigrate([
			$em->get('Asgard\Orm\Tests\Fixtures\PersistentCollection\A'),
			$em->get('Asgard\Orm\Tests\Fixtures\PersistentCollection\B'),
		]);

		#Fixtures
		$dataMapper->create('Asgard\Orm\Tests\Fixtures\PersistentCollection\A', [
			'id' => 1,
			'name' => 'foo',
			'b' => [
				$dataMapper->create('Asgard\Orm\Tests\Fixtures\PersistentCollection\B', [
					'name' => 'bar1',
				]),
				$dataMapper->create('Asgard\Orm\Tests\Fixtures\PersistentCollection\B', [
					'name' => 'bar2',
				]),
			],
		]);

		$a = $dataMapper->load('Asgard\Orm\Tests\Fixtures\PersistentCollection\A', 1);
		$collection = $a->get('b');

		$this->assertEquals(2, $collection[0]->id);
		$this->assertEquals(1, $collection[1]->id);
	}

	public function testCount() {
		$em = new \Asgard\Entity\EntityManager;
		$dataMapper = new \Asgard\Orm\DataMapper($db = new \Asgard\Db\DB([
			'driver' => 'sqlite',
			'database' => ':memory:',
		]), $em);
		(new \Asgard\Orm\ORMMigrations($dataMapper))->autoMigrate([
			$em->get('Asgard\Orm\Tests\Fixtures\PersistentCollection\A'),
			$em->get('Asgard\Orm\Tests\Fixtures\PersistentCollection\B'),
		]);

		#Fixtures
		$dataMapper->create('Asgard\Orm\Tests\Fixtures\PersistentCollection\A', [
			'id' => 1,
			'name' => 'foo',
			'b' => [
				$b1 = $dataMapper->create('Asgard\Orm\Tests\Fixtures\PersistentCollection\B', [
					'name' => 'bar1',
				]),
				$b2 = $dataMapper->create('Asgard\Orm\Tests\Fixtures\PersistentCollection\B', [
					'name' => 'bar2',
				]),
			],
		]);

		$a = $dataMapper->load('Asgard\Orm\Tests\Fixtures\PersistentCollection\A', 1);
		$collection = $a->get('b');

		$this->assertEquals(2, $collection->count());

		$collection->remove($b1);
		$this->assertEquals(1, $collection->count());

		$collection->add($em->make('Asgard\Orm\Tests\Fixtures\PersistentCollection\B', ['name' => 'bar3']));
		$this->assertEquals(2, $collection->count());
	}

	public function testSetAll() {
		$em = new \Asgard\Entity\EntityManager;
		$dataMapper = new \Asgard\Orm\DataMapper($db = new \Asgard\Db\DB([
			'driver' => 'sqlite',
			'database' => ':memory:',
		]), $em);
		(new \Asgard\Orm\ORMMigrations($dataMapper))->autoMigrate([
			$em->get('Asgard\Orm\Tests\Fixtures\PersistentCollection\A'),
			$em->get('Asgard\Orm\Tests\Fixtures\PersistentCollection\B'),
		]);

		#Fixtures
		$dataMapper->create('Asgard\Orm\Tests\Fixtures\PersistentCollection\A', [
			'id' => 1,
			'name' => 'foo',
			'b' => [
				$dataMapper->create('Asgard\Orm\Tests\Fixtures\PersistentCollection\B', [
					'name' => 'bar1',
				]),
				$dataMapper->create('Asgard\Orm\Tests\Fixtures\PersistentCollection\B', [
					'name' => 'bar2',
				]),
			],
		]);

		$a = $dataMapper->load('Asgard\Orm\Tests\Fixtures\PersistentCollection\A', 1);
		$collection = $a->get('b');

		$collection->setAll([
			$dataMapper->create('Asgard\Orm\Tests\Fixtures\PersistentCollection\B', [
				'name' => 'bar3',
			]),
		]);
		$dataMapper->save($a);

		$this->assertEquals('bar3', $collection[0]->name);
	}
}