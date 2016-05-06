<?php
namespace Asgard\Orm\Tests;

class ProxyEntityTest extends \PHPUnit_Framework_TestCase {
	public function testLazyLoading() {
		#Dependencies
		$em = new \Asgard\Entity\EntityManager;
		$rulesRegistry = new \Asgard\Validation\RulesRegistry;
		$em->setValidatorFactory(new \Asgard\Validation\ValidatorFactory($rulesRegistry));
		$db = new \Asgard\Db\DB([
			'driver' => 'sqlite',
			'database' => ':memory:',
		]);
		$dataMapper = new \Asgard\Orm\DataMapper($db, $em);

		#Create table for entity
		(new \Asgard\Orm\ORMMigrations($dataMapper))->autoMigrate([
			$em->get('Asgard\Orm\Tests\Fixtures\ProxyEntity\A'),
			$em->get('Asgard\Orm\Tests\Fixtures\ProxyEntity\B'),
		]);

		#Fixtures
		$dataMapper->create('Asgard\Orm\Tests\Fixtures\ProxyEntity\A', [
			'id' => 1,
			'name'=>'foo',
			'b' => $dataMapper->create('Asgard\Orm\Tests\Fixtures\ProxyEntity\B', [
				'name' => 'bar',
			])
		]);

		$a = $dataMapper->load('Asgard\Orm\Tests\Fixtures\ProxyEntity\A', 1);
		$this->assertEquals('bar', $a->b->name);
	}
}