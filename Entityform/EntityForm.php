<?php
namespace Asgard\Entityform;

/**
 * Create form from an entity.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class EntityForm extends \Asgard\Form\Form implements EntityFormInterface {
	/**
	 * Entity.
	 * @var \Asgard\Entity\Entity
	 */
	protected $entity;
	/**
	 * Form locales.
	 * @var array
	 */
	protected $locales = [];
	/**
	 * Fields solver.
	 * @var EntityFieldsSolverInterface
	 */
	protected $entityFieldsSolver;
	/**
	 * Datamapper dependency.
	 * @var \Asgard\Orm\DataMapperInterface
	 */
	protected $dataMapper;


	/**
	 * Constructor.
	 * @param \Asgard\Entity\Entity  $entity
	 * @param array                  $options
	 * @param \Asgard\Http\Request   $request
	 * @param EntityFieldsSolverInterface     $entityFieldsSolver
	 * @param \Asgard\Orm\DataMapperInterface $dataMapper
	 */
	public function __construct(
		\Asgard\Entity\Entity  $entity,
		array                  $options            = [],
		\Asgard\Http\Request   $request            = null,
		EntityFieldsSolverInterface     $entityFieldsSolver = null,
		\Asgard\Orm\DataMapperInterface $dataMapper         = null
	) {
		$this->entityFieldsSolver = $entityFieldsSolver;
		$this->dataMapper         = $dataMapper;
		$this->entity             = $entity;
		$this->locales            = isset($options['locales']) ? $options['locales']:[];

		$fields = [];
		foreach($entity->getDefinition()->properties() as $name=>$property) {
			if(isset($options['only']) && !in_array($name, $options['only']))
				continue;
			if(isset($options['except']) && in_array($name, $options['except']))
				continue;
			if($property->get('editable') === false || $property->get('form_editable') === false)
				continue;

			if($this->locales && $property->get('i18n')) {
				$i18ngroup = [];
				foreach($this->locales as $locale)
					$i18ngroup[$locale] = $this->getPropertyField($entity, $name, $property, $locale);
				$fields[$name] = $i18ngroup;
			}
			else
				$fields[$name] = $this->getPropertyField($entity, $name, $property);
		}

		parent::__construct(
			isset($options['name']) ? $options['name']:$entity->getDefinition()->getShortName(),
			$options,
			$request,
			$fields
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addEntityFieldsSolver($entityFieldsSolver) {
		$this->entityFieldsSolver->addSolver($entityFieldsSolver);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityFieldsSolver() {
		if(!$this->entityFieldsSolver)
			$this->entityFieldsSolver = new EntityFieldsSolver;

		return $this->entityFieldsSolver;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDataMapper(\Asgard\Orm\DataMapperInterface $dataMapper) {
		$this->dataMapper = $dataMapper;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDataMapper() {
		return $this->dataMapper;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntity() {
		return $this->entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addRelation($name) {
		$entity = $this->entity;
		$dataMapper = $this->dataMapper;
		if(!$dataMapper)
			throw new \Exception('Entity form needs a dataMapper to add relations.');

		$relation = $dataMapper->relation($entity->getDefinition(), $name);

		$ids = [''=>$this->getTranslator()->trans('Choose')];
		$orm = $dataMapper->orm($relation->get('entity'));
		while($v = $orm->next())
			$ids[$v->id] = (string)$v;

		if($relation->get('many')) {
			$this->add(new \Asgard\Form\Fields\MultipleSelectField([
				'type'    => 'integer',
				'choices' => $ids,
				'default' => ($entity->isOld() ? $dataMapper->related($entity, $name)->ids():[]),
			]), $name);
		}
		else {
			$this->add(new \Asgard\Form\Fields\SelectField([
				'type'    => 'integer',
				'choices' => $ids,
				'default' => ($entity->isOld() && $entity->get($name) ? $entity->get($name)->id:null),
			]), $name);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function doSave() {
		if($this->dataMapper)
			$this->dataMapper->save($this->entity);
		else
			parent::doSave();
	}

	/**
	 * {@inheritDoc}
	 */
	public function errors($group=null) {
		if(!$this->sent())
			return [];

		if(!$group)
			$group = $this;

		$errors = [];
		if($group instanceof \Asgard\Form\Group) {
			if($group instanceof static)
				$errors = $group->myErrors();
			elseif($group instanceof \Asgard\Form\Group)
				$errors = $group->errors();
			else
				throw new \Exception('The field should not be a: '.get_class($group));

			foreach($group as $name=>$sub_field) {
				if($sub_field instanceof \Asgard\Form\Group) {
					$group_errors = $this->errors($sub_field);
					if(count($group_errors) > 0)
						$errors[$name] = $group_errors;
				}
			}
		}

		$this->setErrors($errors);
		$this->errors = $errors;

		return $errors;
	}

	/* Internal */
	/**
	 * Get the field for a property.
	 * @param  \Asgard\Entity\Entity   $entity
	 * @param  string                  $name
	 * @param  \Asgard\Entity\Property $property
	 * @param  string                  $locale
	 * @return \Asgard\FormInterface\Field
	 */
	protected function getPropertyField(\Asgard\Entity\Entity $entity, $name, \Asgard\Entity\Property $property, $locale=null) {
		$field = $this->getEntityFieldsSolver()->solve($property);

		if($field instanceof \Asgard\Form\DynamicGroup) {
			$field->setCallback(function() use($entity, $name, $property, $locale) {
				$field = $this->getEntityFieldsSolver()->doSolve($property);
				$options = $this->getEntityFieldOptions($property);
				$field->setOptions($options);
				return $field;
			});
		}
		elseif($field instanceof \Asgard\Form\Field) {
			$options = $this->getEntityFieldOptions($property);
			$options['default'] = $this->getDefaultValue($entity, $name, $property, $locale);
			$field->setOptions($options);
		}

		return $field;
	}

	/**
	 * Get the options of a property.
	 * @param  \Asgard\Entity\Property $property
	 * @return array
	 */
	protected function getEntityFieldOptions(\Asgard\Entity\Property $property) {
		$options = [];

		$options['form'] = $this;

		if(isset($property->get('form')['validation'])) {
			$options['validation'] = $property->get('form')['validation'];
			if(isset($property->get('form')['messages']))
				$options['messages'] = $property->get('form')['messages'];
		}

		return $options;
	}

	/**
	 * Get the default value of a property.
	 * @param  \Asgard\Entity\Entity   $entity
	 * @param  string                  $name
	 * @param  \Asgard\Entity\Property $property
	 * @param  string                  $locale
	 * @return mixed
	 */
	protected function getDefaultValue(\Asgard\Entity\Entity $entity, $name, $property, $locale) {
		if($property->get('form.hidden'))
			return '';
		elseif($entity->get($name, $locale) !== null)
			return $entity->get($name, $locale);
	}

	/**
	 * Return its own errors.
	 * @return array
	 */
	protected function myErrors() {
		$data = $this->data();
		$data = array_filter($data, function($v) {
			if($v instanceof \Asgard\Http\HttpFile && $v->error())
				return false;
			return $v !== null;
		});
		if($this->locales) {
			foreach($data as $name=>$value) {
				foreach($this->locales as $locale)
					$this->entity->set($name, $value, $locale);
			}
		}
		else
			$this->entity->set($data);

		return array_merge(parent::myErrors(), $this->entity->errors());
	}
}
