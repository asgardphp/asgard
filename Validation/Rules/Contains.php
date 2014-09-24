<?php
namespace Asgard\Validation\Rules;

/**
 * Check that the input contains a given element.
 */
class Contains extends \Asgard\Validation\Rule {
	/**
	 * Element.
	 * @var mixed
	 */
	public $contain;

	/**
	 * Constructor.
	 * @param mixed $contain
	 */
	public function __construct($contain) {
		$this->contain = $contain;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		return strpos($input, $this->contain) !== false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute must contain ":contain".';
	}
}