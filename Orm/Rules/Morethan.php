<?php
namespace Asgard\Orm\Rules;

/**
 * Verify that there more less than x entities.
 */
class Morethan extends \Asgard\Validation\Rule {
	/**
	 * Minimum number of entities
	 * @var integer
	 */
	public $more;

	/**
	 * Constructor.
	 * @param integer $more
	 */
	public function __construct($more) {
		$this->more = $more;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($input) {
		return $input->count() > $this->more;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMessage() {
		return ':attribute must have more than :more elements.';
	}
}