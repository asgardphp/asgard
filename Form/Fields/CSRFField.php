<?php
namespace Asgard\Form\Fields;

/**
 * CSRF field.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class CSRFField extends \Asgard\Form\Fields\HiddenField {
	protected $session;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(array $options=[], \Asgard\Common\Session $session=null) {
		parent::__construct($options);
		$this->session = $session;
		$this->options['validation']['required'] = true;
		$this->options['validation']['callback'] = [[$this, 'valid']];
		$this->options['messages']['required'] = 'CSRF token is invalid.';
		$this->options['messages']['callback'] = 'CSRF token is invalid.';

		$this->widget = function($field, $options) {
			$token = $this->generateToken();
			return $field->getParent()->getWidget('Asgard\Form\Widgets\HiddenWidget', $field->name(), $token, $options)->render();
		};
	}

	protected function getSession() {
		if(!$this->session) {
			if(php_sapi_name() === 'cli')
				$this->session = new \Asgard\Common\Bag;
			else
				$this->session = \Asgard\Common\Session::singleton();
		}
		return $this->session;
	}

	/**
	 * Generate a new token.
	 * @return string
	 */
	protected function generateToken() {
		$session = $this->getSession();
		if($session->has('_csrf_token'))
			return $session['_csrf_token'];
		else {
			$token = \Asgard\Common\Tools::randstr();
			return $session['_csrf_token'] = $token;
		}
	}

	/**
	 * Validation callback.
	 * @return boolean
	 */
	public function valid() {
		$session = $this->getSession();
		return $this->value == $session['_csrf_token'];
	}
}