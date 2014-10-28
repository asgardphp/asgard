<?php
namespace Asgard\Orm\Rules;

/**
 * Verify that there more less than x entities.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Ormhasmorethan extends \Asgard\Validation\Rule {
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
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		return $input->count() > $this->more;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute must have more than :more elements.';
	}
}