<?php
namespace Asgard\Entityform;

/**
 * Create form from an entity.
 */
class EntityForm extends \Asgard\Form\Form {
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
	 * @var EntityFieldsSolver
	 */
	protected $entityFieldsSolver;

	
	/**
	 * Constructor.
	 * @param \Asgard\Entity\Entity $entity
	 * @param array                 $options
	 * @param \Asgard\Http\Request  $request
	 * @param EntityFieldsSolver    $entityFieldsSolver
	 */
	public function __construct(
		\Asgard\Entity\Entity $entity,
		array $options                  = [],
		\Asgard\Http\Request $request   = null,
		$entityFieldsSolver             = null
	) {
		$this->entityFieldsSolver = $entityFieldsSolver;
		$this->entity             = $entity;
		$this->locales            = isset($options['locales']) ? $options['locales']:[];
	
		$fields = [];
		foreach($entity::getStaticDefinition()->properties() as $name=>$property) {
			if(isset($options['only']) && !in_array($name, $options['only']))
				continue;
			if(isset($options['except']) && in_array($name, $options['except']))
				continue;
			if($property->editable === false || $property->form_editable === false)
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
			isset($options['name']) ? $options['name']:$entity::getStaticDefinition()->getShortName(),
			$options,
			$request,
			$fields
		);
	}

	/**
	 * Add another nested fields solver.
	 * @param EntityFieldsSolver $entityFieldsSolver
	 */
	public function addEntityFieldsSolver($entityFieldsSolver) {
		$this->entityFieldsSolver->addSolver($entityFieldsSolver);
	}

	/**
	 * Return the main fields solver.
	 * @return EntityFieldsSolver
	 */
	public function getEntityFieldsSolver() {
		if(!$this->entityFieldsSolver)
			$this->entityFieldsSolver = new EntityFieldsSolver;

		return $this->entityFieldsSolver;
	}

	/**
	 * Return the entity.
	 * @return \Asgard\Entity\Entity
	 */
	public function getEntity() {
		return $this->entity;
	}

	/* Entity fields */
	/**
	 * Embed an entity relation in the form.
	 * @param string $name
	 */
	public function addRelation($name) {
		$entity = $this->entity;
		$relation = $entity::getStaticDefinition()->relation($name);

		$ids = [''=>$this->getTranslator()->trans('Choose')];
		$orm = $relation['entity']::orm();
		while($v = $orm->next())
			$ids[$v->id] = (string)$v;
		
		if($relation['has'] == 'one') {
			$this->add(new \Asgard\Form\Fields\SelectField([
				'type'      =>	'integer',
				'choices'   =>	$ids,
				'default'   =>	($this->entity->isOld() && $this->entity->$name ? $this->entity->$name->id:null),
			]), $name);
		}
		elseif($relation['has'] == 'many') {
			$this->add(new \Asgard\Form\Fields\MultipleSelectField([
				'type'      =>	'integer',
				'choices'   =>	$ids,
				'default'   =>	($this->entity->isOld() ? $this->entity->$name()->ids():[]),
			]), $name);
		}
	}
	
	/* Save & Validation */
	/**
	 * Save the entity.
	 * @return boolean true for success
	 */
	public function doSave() {
		$entity = $this->entity;
		if($entity::getStaticDefinition()->hasBehavior('Asgard\Entity\PersistenceBehavior'))
			$entity->save();
		else
			parent::doSave();
	}

	/**
	 * Return the errors of a nested-group if provided, or all.
	 * @param  null|\Asgard\Form\Group $field
	 * @return array
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
	 * @return \Asgard\Form\Field
	 */
	protected function getPropertyField(\Asgard\Entity\Entity $entity, $name, \Asgard\Entity\Property $property, $locale=null) {
		$field = $this->getEntityFieldsSolver()->solve($property);

		if($field instanceof \Asgard\Form\DynamicGroup) {
			$field->setCallback(function() use($entity, $name, $property, $locale) {
				$field = $this->getEntityFieldsSolver()->doSolve($property);
				$options = $this->getEntityFieldOptions($property);
				$field->setoptions($options);
				return $field;
			});
		}
		else {
			$options = $this->getEntityFieldOptions($property);
			$options['default'] = $this->getDefaultValue($entity, $name, $property, $locale);
			$field->setoptions($options);
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
		if($this->locales)
			$this->entity->set($data, $this->locales);
		else
			$this->entity->set($data);

		return array_merge(parent::myErrors(), $this->entity->errors());
	}
}
