<?php
namespace Asgard\Form\Fields;

class CSRFField extends \Asgard\Form\Fields\HiddenField {
	function __construct($options=array()) {
		parent::__construct($options);
		$this->options['validation']['csrf_check'] = array($this, 'error');

		$this->default_render = function($field, $options) {
			$token = $this->generateToken();
			return \Asgard\Form\Widgets\HTMLWidget::getWidget('Asgard\Form\Widgets\HiddenWidget', array($field->getName(), $token, $options))->render();
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
		if($this->value != $this->dad->getRequest()->session->get('_csrf_token')) {
			if(function_exists('__'))
				return __('CSRF token is invalid.');
			else
				return 'CSRF token is invalid.';
		}
	}
}