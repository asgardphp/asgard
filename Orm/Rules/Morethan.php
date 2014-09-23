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
	public function validate($input, $parentInput, $validator) {
		$entity = $validator->get('entity');
		$dataMapper = $validator->get('dataMapper');
		$relation = $validator->getName();
		if($entity->data['properties'][$relation] instanceof \Asgard\Entity\ManyCollection)
			return $entity->data['properties'][$relation]->count() > $this->more;
		else
			return $dataMapper->related($entity, $relation)->count() > $this->more;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMessage() {
		return ':attribute must have more than :more elements.';
	}
}