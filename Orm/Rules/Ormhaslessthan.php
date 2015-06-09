<?php
namespace Asgard\Orm\Rules;

/**
 * Verify that there are less than x entities.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Ormhaslessthan extends \Asgard\Validation\Rule {
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
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		$entity = $validator->get('entity');
		$dataMapper = $validator->get('dataMapper');
		$attr = $validator->getName();
		$orm = $dataMapper->related($entity, $attr);

		return $orm->count() < $this->less;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute must have less than :less elements.';
	}
}