<?php
namespace Asgard\Validation\Rules;

/**
 * Check that the input countains more than a given number.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Hasmorethan extends \Asgard\Validation\Rule {
	/**
	 * Count.
	 * @var integer
	 */
	public $count;

	/**
	 * Constructor.
	 * @param integer $count
	 */
	public function __construct($count) {
		$this->count = $count;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		return count($input) > $this->count;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute must have more than :count elements.';
	}
}