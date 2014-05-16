<?php
namespace Asgard\Form\Fields;

class BooleanField extends \Asgard\Form\Field {
	protected $default_render = 'checkbox';

	public function getValue() {
		return !!$this->value;
	}
}