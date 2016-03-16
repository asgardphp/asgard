<?php
namespace Asgard\Form\Field;

/**
 * Month field.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class MonthField extends \Asgard\Form\Field\SelectField {
	/**
	 * {@inheritDoc}
	 */
	public function __construct(array $options=[]) {
		$options['validation']['type'] = 'integer';
		$options['choices'] = ['Month'];
		foreach(range(1, 12) as $i)
			$options['choices'][$i] = $i;
		parent::__construct($options);
	}
}