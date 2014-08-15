<?php
namespace Asgard\Form\Fields;

class CSRFField extends \Asgard\Form\Fields\HiddenField {
	public function __construct(array $options=[]) {
		parent::__construct($options);
		$this->options['validation']['required'] = true;
		$this->options['validation']['callback'] = [[$this, 'valid']];
		$this->options['messages']['required'] = 'CSRF token is invalid.';
		$this->options['messages']['callback'] = 'CSRF token is invalid.';

		$this->widget = function($field, $options) {
			$token = $this->generateToken();
			return $field->getParent()->getWidget('Asgard\Form\Widgets\HiddenWidget', $field->name(), $token, $options)->render();
		};
	}

	protected function generateToken() {
		if($this->parent->getRequest()->session->has('_csrf_token'))
			return $this->parent->getRequest()->session['_csrf_token'];
		else {
			$token = \Asgard\Common\Tools::randstr();
			return $this->parent->getRequest()->session['_csrf_token'] = $token;
		}
	}

	public function valid() {
		return $this->value == $this->parent->getRequest()->session['_csrf_token'];
	}
}