<?php
namespace Asgard\Validation\Rule;

/**
 * Check that the whole input is in a given array.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Allin extends \Asgard\Validation\Rule {
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
		foreach($input as $v) {
			if(!in_array($v, $this->in))
				return false;
		}
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute is invalid.';
	}
}