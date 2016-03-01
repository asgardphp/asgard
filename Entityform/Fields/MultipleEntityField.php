<?php
namespace Asgard\Entityform\Fields;

/**
 * File field.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class MultipleEntityField extends \Asgard\Form\Field {
	/**
	 * {@inheritDoc}
	 */
	protected $widget = 'multipleselect';
	/**
	 * ORM.
	 * @var \Asgard\Orm\ORMInterface
	 */
	protected $orm;
	/**
	 * Array of entities.
	 * @var array
	 */
	protected $entities;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(array $options=[]) {
		parent::__construct($options);

		if(!isset($this->options['orm']))
			throw new \Exception('Option "orm" must be given.');
		$orm = $this->orm = $this->options['orm'];

		$this->options['choices'] = [];
		foreach($orm as $e)
			$this->options['choices'][$e->id] = (string)$e;
	}

	/**
	 * {@inheritDoc}
	 */
	public function value() {
		$ids = $this->value;
		if($ids !== null && $this->entities === null) {
			$this->entities = [];
			foreach($ids as $id)
				$this->entities[] = $this->orm->load($id);
		}

		return $this->entities;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValidationRules() {
		$validation = parent::getValidationRules();
		if(isset($this->options['choices']))
			$validation['entitiesexist'] = [$this->orm];

		return $validation;
	}
}