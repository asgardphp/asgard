<?php
namespace Asgard\Form\Field;

/**
 * Day field.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class DayField extends \Asgard\Form\Field\SelectField {
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