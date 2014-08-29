<?php
namespace Asgard\Orm\Rules;

/**
 * Verify that there are less than x entities.
 */
class Lessthan extends \Asgard\Validation\Rule {
	/**
	 * Maximum number of entities
	 * @var integer
	 */
	public $less;

	/**
	 * Constructor.
	 * @param integer $less
	 */
	public function __construct($less) {
		$this->less = $less;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($input) {
		return $input->count() < $this->less;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMessage() {
		return ':attribute must have less than :less elements.';
	}
}