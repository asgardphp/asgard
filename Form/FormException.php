<?php
namespace Asgard\Form;

/**
 * Exception for form errors.
 */
class FormException extends \Exception {
	/**
	 * Errors.
	 * @var array
	 */
	public $errors = [];
}