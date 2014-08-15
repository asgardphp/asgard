<?php
namespace Asgard\Form\Tests;

class SelectFieldTest extends \PHPUnit_Framework_TestCase {
	public function test() {
		$form = new \Asgard\Form\Form;
		$request = new \Asgard\Http\Request;
		$request->setMethod('post');
		$request->post['field'] = '1';
		$form->setRequest($request);
		$form['field'] = new \Asgard\Form\Fields\SelectField(['default' => 'default', 'choices' => ['test', 'default', '1', '2', '3']]);

		$this->assertEquals('<select name="field" id="field"><option value="0">test</option><option value="1" selected="selected">default</option><option value="2">1</option><option value="3">2</option><option value="4">3</option></select>', (string)($form['field']->def()));
		$this->assertEquals('1', $form['field']->value());
		
		foreach($form['field']->getRadios() as $name=>$radio) {
			if($name === 0) {
				$this->assertEquals('<input type="radio" name="field" value="0">', (string)$radio);
				$this->assertEquals('test', $radio->label());
			}
			elseif($name === 1) {
				$this->assertEquals('<input type="radio" name="field" value="1" checked="checked">', (string)$radio);
				$this->assertEquals('default', $radio->label());
			}
		}
	}
}
