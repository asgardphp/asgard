<?php
namespace Asgard\Validation\Rule;

/**
 * Check if the input is the same as another attribute.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Same extends \Asgard\Validation\Rule {
	/**
	 * Other attribute path.
	 * @var string
	 */
	protected $as;

	/**
	 * Constructor.
	 * @param string $as
	 */
	public function __construct($as) {
		$this->as = $as;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		return $input == $parentInput->attribute($this->as)->input();
	}

	/**
	 * {@inheritDoc}
	 */
	public function formatParameters(array &$params) {
		$params['as'] = explode('.', $this->as)[count($this->as)-1];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute must be same as :as.';
	}
}