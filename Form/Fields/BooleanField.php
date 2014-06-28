<?php
namespace Asgard\Form\Fields;

class BooleanField extends \Asgard\Form\Field {
	protected $widget = 'checkbox';

	public function value() {
		return !!$this->value;
	}
}