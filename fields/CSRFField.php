<?php
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
		if(\Asgard\Core\App::get('session')->has('_csrf_token'))
			return \Asgard\Core\App::get('session')->get('_csrf_token');
		else {
			$token = \Asgard\Utils\Tools::randstr();
			\Asgard\Core\App::get('session')->set('_csrf_token', $token);
			return $token;
		}
	}

	public function error($attr, $value) {
		if($this->value != \Asgard\Core\App::get('session')->get('_csrf_token'))
			return __('CSRF token is invalid.');
	}
}