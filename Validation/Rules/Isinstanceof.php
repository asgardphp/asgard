<?php
namespace Asgard\Validation\Rules;

/**
 * Check if the object is instance of a class.
 */
class Isinstanceof extends \Asgard\Validation\Rule {
	/**
	 * Class name.
	 * @var string
	 */
	public $class;

	/**
	 * Constructor.
	 * @param string $class Class to be instance of.
	 */
	public function __construct($class) {
		$this->class = $class;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		return $input instanceof $this->class;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute must be an instance of ":class".';
	}
}