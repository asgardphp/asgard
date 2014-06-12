<?php
namespace Asgard\Form;

class EntityForm extends Form {
	protected $entity;
	protected $locales = [];

	public function __construct(
		\Asgard\Entity\Entity $entity, 
		array $params=[],
		\Asgard\Http\Request $request=null
	) {
		$this->entity = $entity;

		$this->locales = isset($params['locales']) ? $params['locales']:[];
	
		$fields = [];
		foreach($entity->properties() as $name=>$property) {
			if(isset($params['only']) && !in_array($name, $params['only']))
				continue;
			if(isset($params['except']) && in_array($name, $params['except']))
				continue;
			if($property->editable === false || $property->form_editable === false)
				continue;

			if($this->locales && $property->get('i18n')) {
				$i18ngroup = [];
				foreach($this->locales as $locale)
					$i18ngroup[$locale] = $this->addAttributeField($entity, $name, $property, $locale);
				$fields[$name] = $i18ngroup;
			}
			else
				$fields[$name] = $this->addAttributeField($entity, $name, $property);
		}

		parent::__construct(
			isset($params['name']) ? $params['name']:$entity->getShortName(),
			$params,
			$fields,
			$request
		);
	}

	protected function addAttributeField(\Asgard\Entity\Entity $entity, $name, \Asgard\Entity\Property $property, $locale=null) {
		$field_params = [];

		$field_params['form'] = $this;

		return $this->getAttributeField($entity, $name, $locale, $property, $field_params);
	}

	protected function getMultiple($fieldClass, $property, $field_params) {
		return new DynamicGroup(function($data) use($fieldClass, $property, $field_params) {
			return $this->doGetAttributeField($fieldClass, $property, $field_params);
		});
	}

	protected function getAttributeField($entity, $name, $locale, $property, $field_params) {
		if(method_exists($property, 'getFormField'))
			$fieldClass = $property->getFormField();
		else
			$fieldClass = 'Asgard\Form\Fields\TextField';

		if($property->get('multiple')) {
			$group = $this->getMultiple($fieldClass, $property, $field_params);
			foreach($entity->get($name, $locale) as $k=>$one) {
				$params = $field_params;
				if($property->get('form.hidden'))
					$params['default'] = '';
				elseif($entity->get($name, $locale) !== null)
					$params['default'] = $entity->get($name, $locale)[$k];

				$group[] = $this->doGetAttributeField($fieldClass, $property, $params);
			}
			return $group;
		}
		else {
			if($property->get('form.hidden'))
				$field_params['default'] = '';
			elseif($entity->get($name, $locale) !== null)
				$field_params['default'] = $entity->get($name, $locale);

			return $this->doGetAttributeField($fieldClass, $property, $field_params);
		}
	}

	protected function doGetAttributeField($class, $property, $params) {
		if(isset($property->get('form')['validation'])) {
			$params['validation'] = $property->get('form')['validation'];
			if(isset($property->get('form')['messages']))
				$params['messages'] = $property->get('form')['messages'];
		}

		$field = new $class($params);

		return $field;
	}

	public function addRelation($name) {
		$entity = $this->entity;
		$relation = $entity::getDefinition()->relation($name);

		$ids = [''=>$this->getTranslator()->trans('Choose')];
		foreach($relation['entity']::all() as $v)
			$ids[$v->id] = (string)$v;
				
		if($relation['has'] == 'one') {
			$this->addField(new Fields\SelectField([
				'type'	=>	'integer',
				'choices'		=>	$ids,
				'default'	=>	($this->entity->isOld() && $this->entity->$name ? $this->entity->$name->id:null),
			]), $name);
		}
		elseif($relation['has'] == 'many') {
			$this->addField(new Fields\MultipleSelectField([
				'type'	=>	'integer',
				'choices'		=>	$ids,
				'default'	=>	($this->entity->isOld() ? $this->entity->$name()->ids():[]),
			]), $name);
		}
	}
	
	public function errors($field=null) {
		if(!$this->isSent())
			return [];

		if(!$field)
			$field = $this;

		if($field instanceof Group) {
			if($field instanceof static)
				$errors = $field->myErrors();
			elseif($field instanceof Group)
				$errors = $field->errors();
				
			foreach($field as $name=>$sub_field) {
				if($sub_field instanceof Group) {
					$field_errors = $this->errors($sub_field);
					if(count($field_errors) > 0)
						$errors[$sub_field->name] = $field_errors;
				}
			}
		}
		
		$this->setErrors($errors);
		$this->errors = $errors;

		return $errors;
	}
	
	public function getEntity() {
		return $this->entity;
	}
	
	protected function myErrors() {
		$data = $this->getData();
		$data = array_filter($data, function($v) {
			if($v instanceof \Asgard\Form\HttpFile && $v->error())
				return false;
			return $v !== null;
		});
		if($this->locales)
			$this->entity->set($data, 'all');
		else
			$this->entity->set($data);

		return array_merge(parent::myErrors(), $this->entity->errors());
	}
	
	public function save() {
		if($errors = $this->errors()) {
			$e = new FormException;
			$e->errors = $errors;
			throw $e;
		}
		if(!$this->isSent())
			return;
	
		$this->trigger('pre_save');
	
		return $this->_save();
	}
	
	protected function _save($group=null) {
		if(!$group)
			$group = $this;

		if($group instanceof static)
			$group->entity->save();

		if($group instanceof Group) {
			foreach($group->getFields() as $name=>$field) {
				if($field instanceof Group)
					$group->_save($field);
			}
		}
	}
}
