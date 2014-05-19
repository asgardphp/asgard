<?php
namespace Asgard\Form;

class EntityForm extends Form {
	protected $_entity;
	protected $_locales = array();

	public function __construct(
		\Asgard\Entity\Entity $entity, 
		array $params=array(),
		\Asgard\Http\Request $request=null,
		$app=null
	) {
		$this->_entity = $entity;

		$this->_locales = isset($params['locales']) && $params['locales'];
	
		$fields = array();
		foreach($entity->properties() as $name=>$properties) {
			if(isset($params['only']) && !in_array($name, $params['only']))
				continue;
			if(isset($params['except']) && in_array($name, $params['except']))
				continue;
			if($properties->editable === false || $properties->form_editable === false)
				continue;

			if($this->_locales && $properties->i18n) {
				$i18ngroup = array();
				foreach($this->_locales as $locale)
					$i18ngroup[$locale] = $this->addAttributeField($entity, $name, $properties, $locale);
				$fields[$name] = $i18ngroup;
			}
			else
				$fields[$name] = $this->addAttributeField($entity, $name, $properties);
		}

		parent::__construct(
			isset($params['name']) ? $params['name']:$entity->getShortName(),
			$params,
			$fields,
			$request,
			$app
		);
	}

	protected function addAttributeField(\Asgard\Entity\Entity $entity, $name, \Asgard\Entity\Property $properties, $locale=null) {
		$field_params = array();

		$field_params['form'] = $this;

		if($properties->form_hidden)
			$field_params['default'] = '';
		elseif($entity->get($name, $locale) !== null)
			$field_params['default'] = $entity->get($name, $locale);

		$field_type = 'Asgard\Form\Fields\TextField';
		if($properties->type == 'boolean')
			$field_type = 'Asgard\Form\Fields\BooleanField';
		elseif($properties->type == 'file') {
			if($properties->multiple)
				$field_type = 'Asgard\Form\Fields\MultipleFileField';
			else
				$field_type = 'Asgard\Form\Fields\FileField';
		}
		elseif($properties->type == 'date')
			$field_type = 'Asgard\Form\Fields\DateField';

		if($properties->in) {
			foreach($properties->in as $v)
				$field_params['choices'][$v] = $v;
			if($properties->multiple)
				$field_type = 'Asgard\Form\Fields\MultipleselectField';
			else
				$field_type = 'Asgard\Form\Fields\SelectField';
		}

		$field_class = $field_type;

		$field = new $field_class($field_params);

		if($properties->type == 'longtext')
			$field->setDefaultRender('textarea');

		return $field;
	}

	public function addRelation($name) {
		$entity = $this->_entity;
		$relation = $entity::getDefinition()->relation($name);

		$ids = array(''=>$this->getTranslator()->trans('Choose'));
		foreach($relation['entity']::all() as $v)
			$ids[$v->id] = (string)$v;
				
		if($relation['has'] == 'one') {
			$this->addField(new Fields\SelectField(array(
				'type'	=>	'integer',
				'choices'		=>	$ids,
				'default'	=>	($this->_entity->isOld() && $this->_entity->$name ? $this->_entity->$name->id:null),
			)), $name);
		}
		elseif($relation['has'] == 'many') {
			$this->addField(new Fields\MultipleSelectField(array(
				'type'	=>	'integer',
				'choices'		=>	$ids,
				'default'	=>	($this->_entity->isOld() ? $this->_entity->$name()->ids():array()),
			)), $name);
		}
	}
	
	public function errors($field=null) {
		if(!$this->isSent())
			return array();

		if(!$field)
			$field = $this;

		if(is_subclass_of($field, 'Asgard\Form\Group')) {
			if($field instanceof \Asgard\Form\EntityForm)
				$errors = $field->myErrors();
			elseif($field instanceof \Asgard\Form\Form)
				$errors = $field->errors();
				
			foreach($field as $name=>$sub_field) {
				if(is_subclass_of($sub_field, 'Asgard\Form\Group')) {
					$field_errors = $this->errors($sub_field);
					if(count($field_errors) > 0)
						$errors[$sub_field->name] = $field_errors;
				}
			}
		}
		
		$this->setErrors($errors);
		$this->_errors = $errors;

		return $errors;
	}
	
	public function getEntity() {
		return $this->_entity;
	}
	
	protected function myErrors() {
		$data = $this->getData();
		$data = array_filter($data, function($v) {
			return $v !== null; 
		});
		if($this->_locales)
			$this->_entity->set($data, 'all');
		else
			$this->_entity->set($data);

		return array_merge(parent::myErrors(), $this->_entity->errors());
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
			$group->_entity->save();

		if($group instanceof Group) {
			foreach($group->getFields() as $name=>$field) {
				if($field instanceof Group)
					$group->_save($field);
			}
		}
	}
}
