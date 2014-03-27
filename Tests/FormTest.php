<?php
namespace Asgard\Form\Tests;

class FormTest extends \PHPUnit_Framework_TestCase {
	#for entities
	public static function setUpBeforeClass() {
		#requis pour charger les entities/orm
		#autoloader dans le bootstrap
		// \Asgard\Core\App::init();

		if(!defined('_ENV_'))
			define('_ENV_', 'test');
		require_once(_CORE_DIR_.'core.php');
		\Asgard\Core\App::instance(true)->config->set('bundles', array(
			new \Asgard\Orm\Bundle,
		));
		\Asgard\Core\App::loadDefaultApp();
	}

	public function testFormAndGroup() {
		#DynamicGroup
		#data sent to dynamic group "group"
		$request = new \Asgard\Core\Request;
		$request->setMethod('post')->post->set('group', array(
			'd',
			'e',
		));

		#__construct: passe une fonction pour générer le "field" ou "form" correspondant a chaque entrée
		$group = new \Asgard\Form\DynamicGroup(function($data) {
			return new \Asgard\Form\Fields\TextField;
		});
		$form = new \Asgard\Form\Form;
		$form->setRequest($request);
		$form->group = $group;

		#pré-rempli le groupe avec des données/entities existants
		$data = array(
			'a',
			'b',
			'c'
		);
		foreach($data as $v)
			$group[] = new \Asgard\Form\Fields\TextField(array('default' => $v));

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
		$form = new \Asgard\Form\Form('test', array(), array(
			'title' => new \Asgard\Form\Fields\TextField
		));
		$form->setRequest($request = new \Asgard\Core\Request);
		$childForm = new \Asgard\Form\Form('test', array(), array(
			'content' => new \Asgard\Form\Fields\TextField(array('validation' => array('required')))
		));
		// $childForm->setDad($form); #$form->childForm = new Form..
		$form->childForm = $childForm;

		$request->server->set('CONTENT_LENGTH', (int)ini_get('post_max_size')*1024*1024+1);
		$this->assertFalse($form->uploadSuccess());
		$request->server->set('CONTENT_LENGTH', (int)ini_get('post_max_size')*1024*1024);
		$this->assertTrue($form->uploadSuccess());

		$request->setMethod('post')->post->set('test', array(
			'title' => 'abc',
			'childForm' => array(
				'content' => 'bla'
			)
		));
		$form->fetch();
		$this->assertTrue($form->isSent());

		$form->setMethod('get');
		$this->assertFalse($form->isSent());

		$request->setMethod('get')->get->set('test', array(
			'title' => 'abc',
			'childForm' => array(
				'content' => 'bla'
			)
		));
		$form->fetch();
		$this->assertTrue($form->isSent());

		$request->get->remove('test');
		$this->assertFalse($form->isSent());
		$this->assertFalse($form->isValid());

		ob_start();
		$form->open(array(
			'action' => 'www.example.net',
			'attrs' => array('class'=>'test')
		));
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
		$request->setMethod('post')->post->set('test', array(
			'title' => 'abc',
			'childForm' => array(
				'content' => null
			)
		));
		$form->fetch();
		$this->assertEquals(array(
			'childForm' => array(
				'content' => array(
					'required' => 'Content is required.'
				)
			)
		), $form->errors());
		$this->assertFalse($form->isValid());
		$form->csrf();
		$this->assertEquals(array(
			'childForm' => array(
				'content' => array(
					'required' => 'Content is required.'
				)
			),
			'_csrf_token' => array(
				'required' => 'CSRF token is invalid.'
			)
		), $form->errors());

		$this->assertEquals(array(
			'_csrf_token' => array(
				'required' => 'CSRF token is invalid.'
			)
		), $form->getGeneralErrors());

		$form->noCSRF();

		$request->post->set('test', array(
			'title' => 'abc',
			'childForm' => array(
				'content' => 'abc'
			)
		));
		$form->fetch();
		$this->assertTrue($form->isValid());

		$this->assertEquals('<input type="text" name="test[title]" value="abc" id="test-title">', $form->title->def()->__toString());
		$form->setRenderCallback('text', function($field, $options) {
			$options['attrs']['class'] = 'a b c';
			return \Asgard\Form\Widgets\HTMLWidget::text($field->getName(), $field->getValue(), $options);
		});
		$this->assertEquals('<input type="text" name="test[title]" value="abc" id="test-title" class="a b c">', $form->title->def()->__toString());

		$this->assertCount(2, $form->getFields());
		$this->assertEquals(2, $form->size());

		$this->assertEquals('test', $form->getName());
		$this->assertEquals('childForm', $form->childForm->getName());

		$form->reset();
		$this->assertEquals(array(
			'title' => '',
			'childForm' => array(
				'content' => ''
			)
		), $form->getData());

		$this->assertFalse($form->hasFile());
		$form->childForm->file = new \Asgard\Form\Fields\FileField;
		$this->assertTrue($form->hasFile());

		$this->assertEquals($form['childForm']['content'], $form->childForm->content);

		foreach($form as $field)
			$this->assertTrue($field instanceof \Asgard\Form\Fields\Field || $field instanceof \Asgard\Form\Group);

		$this->setExpectedException('Asgard\Form\FormException');
		$form->save();
	}

	public function testHTMLHelper() {
		$this->assertEquals('<test a="b" c="d">bla</test>', \Asgard\Form\HTMLHelper::tag('test', array('a'=>'b', 'c'=>'d'), 'bla'));
		$this->assertEquals('<test a="b" c="d">', \Asgard\Form\HTMLHelper::tag('test', array('a'=>'b', 'c'=>'d')));
	}

	public function testEntityForm() {
		$db = new \Asgard\Db\DB(\Asgard\Core\App::get('config')->get('database'));
		$db->import(__dir__.'/formentities.sql');

		$user = new Entities\User;
		$form = new \Asgard\Form\EntityForm($user);
		$request = new \Asgard\Core\Request;
		$request->setMethod('post')->post->set('user', array('name' => 'Bob'));
		$form->setRequest($request);

		$this->assertEquals($user, $form->getEntity());

		$form->addRelation('comments');
		// d($form->comments);
		// $this->assert();
		return;

		$this->assertEquals(array(), $form->errors());
		$form->save();
		$this->assertEquals($user->name, 'Bob');
		
		$request->post->set('user', array());
		$form->fetch();
		$this->assertEquals(array(
			'name' => '..'
		), $this->errors());
		$this->setExpectedException('Asgard\Form\FormException');
		$form->save();

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
