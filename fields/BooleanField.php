<?php
class BooleanField extends \Coxis\Form\Fields\Field {
	protected $default_render = 'checkbox';

	public function getValue() {
		return !!$this->value;
	}
}