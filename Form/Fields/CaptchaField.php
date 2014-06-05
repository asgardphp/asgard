<?php
namespace Asgard\Form\Fields;

class CaptchaField extends \Asgard\Form\Field {
	public function __construct(array $options=array()) {
		parent::__construct($options);
		$this->options['validation']['required'] = true;
		$this->options['validation']['callback'] = array(array($this, 'error'));

		$this->default_render = function($field, $options) {
			return '<img src="'.$field->dad->getRequest()->url->to('captcha').'">'.
				$field->getTopForm()->getWidget('text', $field->getName(), $field->getValue(), $options)->render();
		};
	}

	public function error() {
		if($this->value != $this->getTopForm()->getRequest()->session['captcha'])
			return false;
	}
}