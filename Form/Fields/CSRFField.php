<?php
namespace Asgard\Form\Fields;

class CSRFField extends \Asgard\Form\Fields\HiddenField {
	public function __construct(array $options=array()) {
		parent::__construct($options);
		$this->options['validation']['required'] = true;
		$this->options['validation']['callback'] = array($this, 'error');
		$this->options['messages']['required'] = 'CSRF token is invalid.';
		$this->options['messages']['callback'] = 'CSRF token is invalid.';

		$this->default_render = function($field, $options) {
			$token = $this->generateToken();
			return \Asgard\Form\Widget::getWidget('Asgard\Form\Widgets\HiddenWidget', array($field->getName(), $token, $options))->render();
		};
	}

	protected function generateToken() {
		if($this->dad->getRequest()->session->has('_csrf_token'))
			$this->dad->getRequest()->session->get('_csrf_token');
		else {
			$token = \Asgard\Utils\Tools::randstr();
			$this->dad->getRequest()->session->set('_csrf_token', $token);
			return $token;
		}
	}

	public function error($attr, $value) {
		return $this->value == $this->dad->getRequest()->session->get('_csrf_token');
	}
}