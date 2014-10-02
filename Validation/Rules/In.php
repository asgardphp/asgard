<?php
namespace Asgard\Validation\Rules;

/**
 * Check that the input is in a given array.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class In extends \Asgard\Validation\Rule {
	/**
	 * Haystack.
	 * @var array
	 */
	public $in;

	/**
	 * Constructor.
	 * @param array $in
	 */
	public function __construct($in) {
		$this->in = $in;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		return in_array($input, $this->in);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute is invalid.';
	}
}