<?php
class CSRFField extends \Coxis\Form\Fields\HiddenField {
	function __construct($options=array()) {
		parent::__construct($options);
		$this->options['validation']['csrf_check'] = array($this, 'error');

		$this->default_render = function($field, $options) {
			$token = $this->generateToken();
			return HTMLWidget::hidden($field->getName(), $token, $options)->render();
		};
	}

	protected function generateToken() {
		if(\Coxis\Core\Facades\Session::has('_csrf_token'))
			return \Coxis\Core\Facades\Session::get('_csrf_token');
		else {
			$token = Tools::randstr();
			\Coxis\Core\Facades\Session::set('_csrf_token', $token);
			return $token;
		}
	}

	public function error($attr, $value) {
		if($this->value != \Coxis\Core\Facades\Session::get('_csrf_token'))
			return __('CSRF token is invalid.');
	}
}