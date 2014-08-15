<?php
namespace Asgard\Form\Tests;

class MultipleSelectFieldTest extends \PHPUnit_Framework_TestCase {
	public function test() {
		$form = new \Asgard\Form\Form;
		$request = new \Asgard\Http\Request;
		$request->setMethod('post');
		$request->post['field'] = [0, 1];
		$form->setRequest($request);
		$form['field'] = new \Asgard\Form\Fields\MultipleSelectField(['choices' => ['test', 'default', '1', '2', '3']]);

		$this->assertEquals([0, 1], $form['field']->value());

		$this->assertEquals('<input type="checkbox" name="field[]" value="0" checked="checked"> Test <input type="checkbox" name="field[]" value="1" checked="checked"> Default <input type="checkbox" name="field[]" value="2"> 1 <input type="checkbox" name="field[]" value="3"> 2 <input type="checkbox" name="field[]" value="4"> 3 ', (string)$form['field']->def());

		$this->assertEquals('<select name="field[]" id="field" multiple="multiple"><option value="0" selected="selected">test</option><option value="1" selected="selected">default</option><option value="2">1</option><option value="3">2</option><option value="4">3</option></select>', (string)$form['field']->multipleselect());

		foreach($form['field']->getCheckboxes() as $name=>$checkbox) {
			if($name === 0) {
				$this->assertEquals('<input type="checkbox" name="field[]" value="0" checked="checked">', (string)$checkbox);
				$this->assertEquals('test', $checkbox->label());
			}
			elseif($name === 1) {
				$this->assertEquals('<input type="checkbox" name="field[]" value="1" checked="checked">', (string)$checkbox);
				$this->assertEquals('default', $checkbox->label());
			}
			elseif($name === 2) {
				$this->assertEquals('<input type="checkbox" name="field[]" value="2">', (string)$checkbox);
				$this->assertEquals('1', $checkbox->label());
			}
		}
	}
}
