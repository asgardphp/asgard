<?php
namespace Asgard\Validation\Rule;

/**
 * Check that the input contains a given element.
 * @author Michel Hognerud <michel@hognerud.com>
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
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		return strpos($input, $this->contain) !== false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute must contain ":contain".';
	}
}