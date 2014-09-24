<?php
namespace Asgard\Form\Fields;

/**
 * Boolean field.
 */
class BooleanField extends \Asgard\Form\Field {
	/**
	 * {@inheritDoc}
	 */
	protected $widget = 'checkbox';

	/**
	 * {@inheritDoc}
	 */
	public function value() {
		return !!$this->value;
	}
}