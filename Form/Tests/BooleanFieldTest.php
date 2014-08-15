<?php
namespace Asgard\Form\Tests;

class BooleanFieldTest extends \PHPUnit_Framework_TestCase {
	public function test() {
		$form = new \Asgard\Form\Form;
		$request = new \Asgard\Http\Request;
		$request->setMethod('post');
		$form->setRequest($request);
		$form['field'] = new \Asgard\Form\Fields\BooleanField(['default' => true]);
		$request->post['field'] = true;
		$this->assertEquals('<input type="checkbox" name="field" value="1" checked="checked">', (string)($form['field']->def()));
		$request->post['field'] = null;
		$form->fetch();
		$this->assertEquals('<input type="checkbox" name="field" value="1">', (string)($form['field']->def()));
		$this->assertEquals(false, $form['field']->value());
	}
}
