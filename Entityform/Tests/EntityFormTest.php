<?php
namespace Asgard\Entityform\Tests;

class EntityFormTest extends \PHPUnit_Framework_TestCase {
	protected static $container;

	#for entities
	public static function setUpBeforeClass() {
		$container = new \Asgard\Container\Container;
		$container['config'] = new \Asgard\Config\Config;
		$container['hooks'] = new \Asgard\Hook\HooksManager($container);
		$container['cache'] = new \Asgard\Cache\NullCache;
		$container['translator'] = new \Symfony\Component\Translation\Translator('en');
		$container['entitiesmanager'] = new \Asgard\Entity\EntitiesManager($container);
		static::$container = $container;
	}

	public function testEntityForm() {
		$user = new Entities\User;
		$form = new \Asgard\Entityform\EntityForm($user, [], null);
		$form->setTranslator(static::$container['translator']);
		$request = new \Asgard\Http\Request;
		$request->setMethod('post')->post->set('user', ['name' => 'Bob']);
		$form->setRequest($request);

		$this->assertEquals($user, $form->getEntity());

		$form->addRelation('comments');
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
