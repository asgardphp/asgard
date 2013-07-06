<?php
class CaptchaField extends \Coxis\Form\Fields\Field {
	function __construct($options=array()) {
		parent::__construct($options);
		$this->options['validation']['captcha_check'] = array($this, 'error');

		$this->default_render = function($field, $options) {
			return '<img src="'.URL::to('captcha').'">'.
				HTMLWidget::text($field->getName(), $field->getValue(), $options)->render();
		};
	}

	public function error() {
		if($this->value != \Session::get('captcha'))
			return __('Captcha is invalid.');
	}
}