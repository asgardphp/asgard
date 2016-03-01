<?php
namespace Asgard\Entityform\Tests;

class EntityFormTest extends \PHPUnit_Framework_TestCase {
	public function testEntityForm() {
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
		$schema->drop('comment');
		(new \Asgard\Orm\ORMMigrations($dataMapper))->autoMigrate([
			$em->get('Asgard\Entityform\Tests\Entities\User'),
			$em->get('Asgard\Entityform\Tests\Entities\Comment')
		]);

		$a = $em->make('Asgard\Entityform\Tests\Entities\Comment', ['id'=>1, 'content'=>'Foo']);

		$dataMapper->save($em->make('Asgard\Entityform\Tests\Entities\Comment', ['id'=>1, 'content'=>'Foo']));
		$dataMapper->save($em->make('Asgard\Entityform\Tests\Entities\Comment', ['id'=>2, 'content'=>'Bar']));
		$dataMapper->save($thirdComment = $em->make('Asgard\Entityform\Tests\Entities\Comment', ['id'=>3, 'content'=>'Zoo']));

		$dataMapper->save($user = $em->make('Asgard\Entityform\Tests\Entities\User', [
			'name' => 'bob',
			'comments' => [
				$thirdComment
			]
		]));

		#Init fORMInterface
		$form = new \Asgard\Entityform\EntityForm($user, [], null, null, $dataMapper);
		$form->setTranslator(new \Symfony\Component\Translation\Translator('en'));
		$request = new \Asgard\Http\Request;
		$request->setMethod('post')->post->set('user', ['name' => 'Bob']);
		$form->setRequest($request);

		#Tests
		$this->assertEquals($user, $form->getEntity());

		$form->addRelation('comments');
		$this->assertInstanceOf('Asgard\Entityform\Fields\MultipleEntityField', $form['comments']);
		$this->assertEquals($form['comments']->options['choices'][1], 'Foo');
		$this->assertEquals($form['comments']->options['choices'][2], 'Bar');
		$this->assertEquals($form['comments']->options['choices'][3], 'Zoo');
		$this->assertTrue(in_array(3, array_map(function($e) { return $e->id; }, $form['comments']->options['default']->all())));
		return;
/*
		$this->assertEquals([], $form->errors());
		$form->save();
		$this->assertEquals($user->name, 'Bob');

		$request->post->set('user', []);
		$form->fetch();
		$this->assertEquals([
			'name' => '..'
		], $this->errors());
		$this->setExpectedException('Asgard\Form\FormException');
		$form->save();*/

		#__construct
		#addRelation
		#getEntity
		#errors
		#save
	}
}
