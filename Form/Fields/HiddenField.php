<?php
namespace Asgard\Form\Fields;

/**
 * Hidden field.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class HiddenField extends \Asgard\Form\Field {
	/**
	 * {@inheritDoc}
	 */
	protected $widget = 'hidden';
}