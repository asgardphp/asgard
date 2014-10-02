<?php
namespace Asgard\Form;

/**
 * Exception for form errors.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class FormException extends \Exception {
	/**
	 * Errors.
	 * @var array
	 */
	public $errors = [];
}