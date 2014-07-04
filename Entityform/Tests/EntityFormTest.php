<?php
namespace Asgard\Entityform\Tests;

class EntityFormTest extends \PHPUnit_Framework_TestCase {
	protected static $app;

	#for entities
	public static function setUpBeforeClass() {
		$app = new \Asgard\Container\Container;
		$app['config'] = new \Asgard\Config\Config;
		$app['hooks'] = new \Asgard\Hook\HooksManager($app);
		$app['cache'] = new \Asgard\Cache\NullCache;
		$app['translator'] = new \Symfony\Component\Translation\Translator('en');
		$app['entitiesmanager'] = new \Asgard\Entity\EntitiesManager($app);
		\Asgard\Entity\Entity::setApp($app);
		static::$app = $app;
	}

	public function testEntityForm() {
		$user = new Entities\User;
		$form = new \Asgard\Entityform\EntityForm($user, [], null);
		$form->setTranslator(static::$app['translator']);
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
