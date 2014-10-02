<?php
namespace Asgard\Validation\Rules;

/**
 * Check that the input matches the given pattern.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Regex extends \Asgard\Validation\Rule {
	/**
	 * Pattern.
	 * @var string
	 */
	public $pattern;

	/**
	 * Constructor.
	 * @param string $pattern
	 */
	public function __construct($pattern) {
		$this->pattern = $pattern;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		return preg_match($this->pattern, $input) === 1;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute must match pattern ":pattern".';
	}
}