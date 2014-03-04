<?php
class CSRFField extends \Coxis\Form\Fields\HiddenField {
	function __construct($options=array()) {
		parent::__construct($options);
		$this->options['validation']['csrf_check'] = array($this, 'error');

		$this->default_render = function($field, $options) {
			$token = $this->generateToken();
			return \Coxis\Form\Widgets\HTMLWidget::getWidget('Coxis\Form\Widgets\HiddenWidget', array($field->getName(), $token, $options))->render();
		};
	}

	protected function generateToken() {
		if(\Coxis\Core\App::get('session')->has('_csrf_token'))
			return \Coxis\Core\App::get('session')->get('_csrf_token');
		else {
			$token = \Coxis\Utils\Tools::randstr();
			\Coxis\Core\App::get('session')->set('_csrf_token', $token);
			return $token;
		}
	}

	public function error($attr, $value) {
		if($this->value != \Coxis\Core\App::get('session')->get('_csrf_token'))
			return __('CSRF token is invalid.');
	}
}