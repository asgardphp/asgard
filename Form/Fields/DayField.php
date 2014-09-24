<?php
namespace Asgard\Form\Fields;

/**
 * Day field.
 */
class DayField extends \Asgard\Form\Fields\SelectField {
	/**
	 * {@inheritDoc}
	 */
	public function __construct(array $options=[]) {
		$options['validation']['type'] = 'integer';
		$options['choices'] = ['Day'];
		foreach(range(1, 31) as $i)
			$options['choices'][$i] = $i;
		parent::__construct($options);
	}
}