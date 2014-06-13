<?php
namespace Asgard\Form\Tests;

class FormTest extends \PHPUnit_Framework_TestCase {
	protected static $app;
	protected static $config = [
		'database' => 'asgard',
		'user' => 'root',
		'password' => '',
		'host' => 'localhost'
	];

	#for entities
	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');

		$app = new \Asgard\Container\Container;
		$app['config'] = new \Asgard\Config\Config;
		$app['hooks'] = new \Asgard\Hook\HooksManager($app);
		$app['cache'] = new \Asgard\Cache\NullCache;
		$app['translator'] = new \Symfony\Component\Translation\Translator('en');
		$app['entitiesmanager'] = new \Asgard\Entity\EntitiesManager($app);
		$app['db'] = new \Asgard\Db\DB(static::$config);
		\Asgard\Entity\Entity::setApp($app);
		static::$app = $app;
	}

	public function testFormAndGroup() {
		#DynamicGroup
		#data sent to dynamic group "group"
		$request = new \Asgard\Http\Request;
		$request->setMethod('post')->post->set('group', [
			'd',
			'e',
		]);

		#__construct: passe une fonction pour générer le "field" ou "form" correspondant a chaque entrée
		$group = new \Asgard\Form\DynamicGroup(function($data) {
			return new \Asgard\Form\Fields\TextField;
		});
		$form = new \Asgard\Form\Form;
		$form->setRequest($request);
		$form->setHooks(static::$app['hooks']);
		$form->setTranslator(static::$app['translator']);
		$form['group'] = $group;

		#pré-rempli le groupe avec des données/entities existants
		$data = [
			'a',
			'b',
			'c'
		];
		foreach($data as $v)
			$group[] = new \Asgard\Form\Fields\TextField(['default' => $v]);

		#setDefaultRender definit la fonction par defaut pour generer le code HTML d'une entree du formulaire
		#optionnelle vu qu'on utilise $field->def()
		$group->setDefaultRender(function($field) {
			return $field->def();
		});

		$this->assertCount(5, $group->getFields());

		#pour l'affichage : une simple boucle sur toutes les entrees du groupe, et affichage avec la fonction def() qui utilise le default render
		foreach($group as $field)
			$this->assertRegExp('<input type="text" name="group\[[0-9]\]" value="[a-z]" id="group-[0-9]">', $field->def()->__toString());

		#affichage de la template pour de nouvelles entrées
		$template = $group->renderTemplate();
		$this->assertEquals('<input type="text" name="group[]" value="" id="group-">', $template);

		#Form
		$form = new \Asgard\Form\Form('test', [], [
			'title' => new \Asgard\Form\Fields\TextField
		], $request);
		$form->setHooks(static::$app['hooks']);
		$form->setTranslator(static::$app['translator']);
		$childForm = new \Asgard\Form\Form('test', [], [
			'content' => new \Asgard\Form\Fields\TextField(['validation' => 'required'])
		]);
		$form['childForm'] = $childForm;

		$request->server->set('CONTENT_LENGTH', (int)ini_get('post_max_size')*1024*1024+1);
		$this->assertFalse($form->uploadSuccess());
		$request->server->set('CONTENT_LENGTH', (int)ini_get('post_max_size')*1024*1024);
		$this->assertTrue($form->uploadSuccess());

		$request->setMethod('post')->post->set('test', [
			'title' => 'abc',
			'childForm' => [
				'content' => 'bla'
			]
		]);
		$form->fetch();
		$this->assertTrue($form->isSent());

		$form->setMethod('get');
		$this->assertFalse($form->isSent());

		$request->setMethod('get')->get->set('test', [
			'title' => 'abc',
			'childForm' => [
				'content' => 'bla'
			]
		]);
		$form->fetch();
		$this->assertTrue($form->isSent());

		$request->get->delete('test');
		$this->assertFalse($form->isSent());
		$this->assertFalse($form->isValid());

		ob_start();
		$form->open([
			'action' => 'www.example.net',
			'attrs' => ['class'=>'test']
		]);
		$this->assertEquals('<form action="www.example.net" method="get" class="test">'."\n", ob_get_clean());

		ob_start();
		$form->close();
		$this->assertRegExp('/<\/form>/', ob_get_clean());
		$form->csrf();
		ob_start();
		$form->close();
		$this->assertRegExp('/<input type="hidden" name="test\[_csrf_token\]" value="[a-zA-Z0-9]+" id="test-_csrf_token"><\/form>/', ob_get_clean());
		$form->noCSRF();

		ob_start();
		$form->submit('Send');
		$this->assertEquals('<input type="submit" value="Send">', ob_get_clean());

		$form->setMethod('post');
		$request->setMethod('post')->post->set('test', [
			'title' => 'abc',
			'childForm' => [
				'content' => null
			]
		]);
		$form->fetch();
		$this->assertEquals([
			'childForm' => [
				'content' => [
					'required' => 'Content is required.'
				]
			]
		], $form->errors());
		$this->assertFalse($form->isValid());
		$form->csrf();
		$this->assertEquals([
			'childForm' => [
				'content' => [
					'required' => 'Content is required.'
				]
			],
			'_csrf_token' => [
				'required' => 'CSRF token is invalid.'
			]
		], $form->errors());

		$this->assertEquals([
			'_csrf_token' => [
				'required' => 'CSRF token is invalid.'
			]
		], $form->getGeneralErrors());

		$form->noCSRF();

		$request->post->set('test', [
			'title' => 'abc',
			'childForm' => [
				'content' => 'abc'
			]
		]);
		$form->fetch();
		$this->assertTrue($form->isValid());

		#deprecated
		// #Set render callback
		// $this->assertEquals('<input type="text" name="test[title]" value="abc" id="test-title">', $form['title']->def()->__toString());
		// $form->setRenderCallback('text', function($field, $options) {
		// 	$options['attrs']['class'] = 'a b c';
		// 	return $field->getTopForm()->getWidget('text', $field->getName(), $field->getValue(), $options);
		// });
		// $this->assertEquals('<input type="text" name="test[title]" value="abc" id="test-title" class="a b c">', $form['title']->def()->__toString());

		$this->assertCount(2, $form->getFields());
		$this->assertEquals(2, $form->size());

		$this->assertEquals('test', $form->getName());
		$this->assertEquals('childForm', $form['childForm']->getName());

		$form->reset();
		$this->assertEquals([
			'title' => '',
			'childForm' => [
				'content' => ''
			]
		], $form->getData());

		$this->assertFalse($form->hasFile());
		$form['childForm']['file'] = new \Asgard\Form\Fields\FileField;
		$this->assertTrue($form->hasFile());

		foreach($form as $field)
			$this->assertTrue($field instanceof \Asgard\Form\Field || $field instanceof \Asgard\Form\Group);

		$this->setExpectedException('Asgard\Form\FormException');
		$form->save();
	}

	public function testHTMLHelper() {
		$this->assertEquals('<test a="b" c="d">bla</test>', \Asgard\Form\HTMLHelper::tag('test', ['a'=>'b', 'c'=>'d'], 'bla'));
		$this->assertEquals('<test a="b" c="d">', \Asgard\Form\HTMLHelper::tag('test', ['a'=>'b', 'c'=>'d']));
	}

	public function testEntityForm() {
		$mysql = new \Asgard\Db\MySQL(static::$config);
		$mysql->import(__DIR__.'/formentities.sql');

		$user = new Entities\User;
		$form = new \Asgard\Form\EntityForm($user, [], null);
		$form->setHooks(static::$app['hooks']);
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

	#EntityForm
	#i18n forms
	#Fields..
	#Widgets..
	#HTMLHelper

	#Group
		/*#to render a field with the group renderer function
		render
		#get all the group fields
		getFields
		#if it has a field
		has / __isset
		#returns the list of parents
		getParents
		#returns the group name
		getName
		#check if the group form was sent
		isSent
		#size of fields
		size
		addFields
		addField
		setDad
		setFields
		setName
		#reset data and files
		reset
		setData
		getData
		#check if the group as file field
		hasFile
		#get the errors
		errors
		#save group and subgroups
		save
		isValid
		remove / __unset
		get / __get
		add / __set
		ArrayAccess: to access fields
		Iterator: through fields
		trigger*/
	#Form (voir Group)
		/*__construct
		k-setDad
		k-uploadSuccess
		k-setMethod
		k-isSent
		k-open
		k-close
		k-submit
		setRenderCallback
		render
		(getMethod)
		getGeneralErrors
		isValid
		*/
}
