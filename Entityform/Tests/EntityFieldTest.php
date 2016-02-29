<?php
namespace Asgard\Entityform\Tests;

class EntityFieldTest extends \PHPUnit_Framework_TestCase {
	public function test() {
		#Dependencies
		$db = new \Asgard\Db\DB([
			'driver' => 'sqlite',
			'database' => ':memory:',
		]);
		$em = new \Asgard\Entity\EntityManager;
		$dataMapper = new \Asgard\Orm\DataMapper($db, $em);

		#Fixtures
		$schema = new \Asgard\Db\Schema($db);
		$schema->drop('user');
		(new \Asgard\Orm\ORMMigrations($dataMapper))->autoMigrate([
			$em->get('Asgard\Entityform\Tests\Entities\User'),
		]);

		$user = $dataMapper->create('Asgard\Entityform\Tests\Entities\User', [
			'id'  => 1,
			'name'=>'bob',
		]);

		$form = new \Asgard\Form\Form;
		$request = new \Asgard\Http\Request;
		$request->setMethod('post');
		$request->post['field'] = '1';
		$form->setRequest($request);

		$form['field'] = new \Asgard\Entityform\Fields\EntityField([
			'orm' => $dataMapper->orm('Asgard\Entityform\Tests\Entities\User'),
		]);

		$this->assertEquals('<select name="field" id="field"><option value="1" selected="selected">bob</option></select>', (string)($form['field']->def()));
		$this->assertInstanceOf('Asgard\Entityform\Tests\Entities\User', $form['field']->value());
	}

	public function testMultiple() {
		#Dependencies
		$db = new \Asgard\Db\DB([
			'driver' => 'sqlite',
			'database' => ':memory:',
		]);
		$em = new \Asgard\Entity\EntityManager;
		$dataMapper = new \Asgard\Orm\DataMapper($db, $em);

		#Fixtures
		$schema = new \Asgard\Db\Schema($db);
		$schema->drop('user');
		(new \Asgard\Orm\ORMMigrations($dataMapper))->autoMigrate([
			$em->get('Asgard\Entityform\Tests\Entities\User'),
		]);

		$user = $dataMapper->create('Asgard\Entityform\Tests\Entities\User', [
			'id'  => 1,
			'name'=>'bob',
		]);
		$user = $dataMapper->create('Asgard\Entityform\Tests\Entities\User', [
			'id'  => 2,
			'name'=>'bob',
		]);

		$form = new \Asgard\Form\Form;
		$request = new \Asgard\Http\Request;
		$request->setMethod('post');
		$request->post['field'] = ['1', '2'];
		$form->setRequest($request);

		$form['field'] = new \Asgard\Entityform\Fields\MultipleEntityField([
			'orm' => $dataMapper->orm('Asgard\Entityform\Tests\Entities\User'),
		]);

		$this->assertEquals('<select name="field[]" id="field" multiple="multiple"><option value="2" selected="selected">bob</option><option value="1" selected="selected">bob</option></select>', (string)($form['field']->def()));
		$this->assertInstanceOf('Asgard\Entityform\Tests\Entities\User', $form['field']->value()[0]);
		$this->assertInstanceOf('Asgard\Entityform\Tests\Entities\User', $form['field']->value()[1]);
	}
}
