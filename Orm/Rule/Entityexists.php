<?php
namespace Asgard\Orm\Rule;

/**
 * Verify that the entity exists in an ORM.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Entityexists extends \Asgard\Validation\Rule {
	/**
	 * Maximum number of entities
	 * @var \Asgard\Orm\ORMInterface
	 */
	public $orm;

	/**
	 * Constructor.
	 * @param \Asgard\Orm\ORMInterface $orm
	 */
	public function __construct($orm) {
		$this->orm = $orm;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		$orm = clone $this->orm;
		return $orm->where('id', $input)->count() > 0;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute is not valid.';
	}
}