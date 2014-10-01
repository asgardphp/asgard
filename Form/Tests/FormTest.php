<?php
namespace Asgard\Form\Tests;

class FormTest extends \PHPUnit_Framework_TestCase {
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

		$this->assertCount(5, $group->fields());

		#pour l'affichage : une simple boucle sur toutes les entrees du groupe, et affichage avec la fonction def() qui utilise le default render
		foreach($group as $field)
			$this->assertRegExp('<input type="text" name="group\[[0-9]\]" value="[a-z]" id="group-[0-9]">', $field->def()->__toString());

		#affichage de la template pour de nouvelles entrées
		$template = $group->renderTemplate();
		$this->assertEquals('<input type="text" name="group[]" value="" id="group-">', $template);

		#FORMInterface
		$form = new \Asgard\Form\Form('test', [], $request);
		$form['title'] = new \Asgard\Form\Fields\TextField;
		$childForm = new \Asgard\Form\Form('test', []);
		$childForm['content'] = new \Asgard\Form\Fields\TextField(['validation' => 'required']);
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
		$this->assertTrue($form->sent());

		$form->setMethod('get');
		$this->assertFalse($form->sent());

		$request->setMethod('get')->get->set('test', [
			'title' => 'abc',
			'childForm' => [
				'content' => 'bla'
			]
		]);
		$form->fetch();
		$this->assertTrue($form->sent());

		$request->get->delete('test');
		$this->assertFalse($form->sent());
		$this->assertFalse($form->isValid());

		$this->assertEquals('<form action="www.example.net" method="get" class="test">'."\n", $form->open([
			'action' => 'www.example.net',
			'attrs' => ['class'=>'test']
		]));

		$this->assertRegExp('/<\/form>/', $form->close());
		$form->csrf();
		$this->assertRegExp('/<input type="hidden" name="test\[_csrf_token\]" value="[a-zA-Z0-9]+" id="test-_csrf_token"><\/form>/', $form->close());
		$form->csrf(false);

		$this->assertEquals('<input type="submit" value="Send">', $form->submit('Send'));

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

		$form->csrf(false);

		$request->post->set('test', [
			'title' => 'abc',
			'childForm' => [
				'content' => 'abc'
			]
		]);
		$form->fetch();
		$this->assertTrue($form->isValid());

		$this->assertCount(2, $form->fields());
		$this->assertEquals(2, $form->size());

		$this->assertEquals('test', $form->name());
		$this->assertEquals('childForm', $form['childForm']->name());

		$form->reset();
		$this->assertEquals([
			'title' => '',
			'childForm' => [
				'content' => ''
			]
		], $form->data());

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

	#EntityForm
	#i18n forms
	#Fields..
	#Widgets..
	#HTMLHelper

	#Group
		/*#to render a field with the group renderer function
		render
		#get all the group fields
		fields
		#if it has a field
		has / __isset
		#returns the list of parents
		getParents
		#returns the group name
		name
		#check if the group form was sent
		sent
		#size of fields
		size
		addFields
		addField
		setParent
		setFields
		setName
		#reset data and files
		reset
		setdata
		data
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
		k-setParent
		k-uploadSuccess
		k-setMethod
		k-sent
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
