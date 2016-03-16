<?php
namespace Asgard\Form\Tests;

class DateFieldTest extends \PHPUnit_Framework_TestCase {
	public function test() {
		$form = new \Asgard\Form\Form;
		$request = new \Asgard\Http\Request;
		$request->setMethod('post');
		$request->post['field'] = ['day'=>20, 'month'=>9, 'year'=>2010];
		$form->setRequest($request);
		$form['field'] = new \Asgard\Form\Field\DateField;

		$r = '<select name="field[day]" id="field-day"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20" selected="selected">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option></select><select name="field[month]" id="field-month"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9" selected="selected">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option></select><select name="field[year]" id="field-year">';
		for($i=date('Y'); $i>=(date('Y')-50); $i--)
			$r .= '<option value="'.$i.'"'.($i == 2010 ? ' selected="selected"':'').'>'.$i.'</option>';
		$r .= '</select>';
		$this->assertEquals($r, (string)($form['field']->def()));
		$this->assertEquals('2010-09-20', $form['field']->value()->format('Y-m-d'));
	}
}
