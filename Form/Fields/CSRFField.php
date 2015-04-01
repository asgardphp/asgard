<?php
namespace Asgard\Form\Fields;

/**
 * CSRF field.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class CSRFField extends \Asgard\Form\Fields\HiddenField {
	/**
	 * {@inheritDoc}
	 */
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

	/**
	 * Generate a new token.
	 * @return string
	 */
	protected function generateToken() {
		$session = \Asgard\Container\Container::singleton()['session'];
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
		$session = \Asgard\Container\Container::singleton()['session'];
		return $this->value == $session['_csrf_token'];
	}
}