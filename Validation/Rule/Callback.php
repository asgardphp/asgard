<?php
namespace Asgard\Validation\Rule;

/**
 * Perform a callback validation.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Callback extends \Asgard\Validation\Rule {
	/**
	 * Callable.
	 * @var callable
	 */
	public $cb;

	/**
	 * Constructor.
	 * @param callable $cb
	 */
	public function __construct($cb) {
		$this->cb = $cb;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		return call_user_func_array($this->cb, [$input, $parentInput, $validator]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute is invalid.';
	}
}