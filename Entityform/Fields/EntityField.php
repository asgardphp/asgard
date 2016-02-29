<?php
namespace Asgard\Entityform\Fields;

/**
 * File field.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class EntityField extends \Asgard\Form\Field {
	/**
	 * {@inheritDoc}
	 */
	protected $widget = 'select';
	/**
	 * ORM.
	 * @var \Asgard\Orm\ORMInterface
	 */
	protected $orm;
	/**
	 * Entity.
	 * @var \Asgard\Entity\Entity
	 */
	protected $entity;

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
		$id = $this->value;
		if($id !== null && $this->entity === null)
			$this->entity = $this->orm->load($id);

		return $this->entity;
	}
}