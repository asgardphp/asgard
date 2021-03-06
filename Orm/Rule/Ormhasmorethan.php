<?php
namespace Asgard\Orm\Rule;

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
		$entity = $validator->get('entity');
		$dataMapper = $validator->get('dataMapper');
		$attr = $validator->getName();
		$orm = $dataMapper->related($entity, $attr);

		return $orm->count() > $this->more;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute must have more than :more elements.';
	}
}