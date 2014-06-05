<?php
namespace Asgard\Form;

class EntityForm extends Form {
	protected $entity;
	protected $locales = array();

	public function __construct(
		\Asgard\Entity\Entity $entity, 
		array $params=array(),
		\Asgard\Http\Request $request=null,
		$app=null
	) {
		$this->entity = $entity;

		$this->locales = isset($params['locales']) ? $params['locales']:array();
	
		$fields = array();
		foreach($entity->properties() as $name=>$property) {
			if(isset($params['only']) && !in_array($name, $params['only']))
				continue;
			if(isset($params['except']) && in_array($name, $params['except']))
				continue;
			if($property->editable === false || $property->form_editable === false)
				continue;

			if($this->locales && $property->get('i18n')) {
				$i18ngroup = array();
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
			$request,
			$app
		);
	}

	protected function addAttributeField(\Asgard\Entity\Entity $entity, $name, \Asgard\Entity\Property $property, $locale=null) {
		$field_params = array();

		$field_params['form'] = $this;

		if($property->form_hidden)
			$field_params['default'] = '';
		elseif($entity->get($name, $locale) !== null)
			$field_params['default'] = $entity->get($name, $locale);

		$field_type = 'Asgard\Form\Fields\TextField';
		if($property->type == 'boolean')
			$field_type = 'Asgard\Form\Fields\BooleanField';
		elseif($property->type == 'file') {
			if($property->get('multiple'))
				$field_type = 'Asgard\Form\Fields\MultipleFileField';
			else
				$field_type = 'Asgard\Form\Fields\FileField';
		}
		elseif($property->type == 'image') {
			if($property->get('multiple'))
				$field_type = 'Asgard\Form\Fields\MultipleImageField';
			else
				$field_type = 'Asgard\Form\Fields\ImageField';
		}
		elseif($property->type == 'date')
			$field_type = 'Asgard\Form\Fields\DateField';

		if($property->in) {
			foreach($property->in as $v)
				$field_params['choices'][$v] = $v;
			if($property->get('multiple'))
				$field_type = 'Asgard\Form\Fields\MultipleselectField';
			else
				$field_type = 'Asgard\Form\Fields\SelectField';
		}

		if(isset($property->get('form')['validation'])) {
			$field_params['validation'] = $property->get('form')['validation'];
			if(isset($property->get('form')['messages']))
				$field_params['messages'] = $property->get('form')['messages'];
		}

		$field_class = $field_type;

		$field = new $field_class($field_params);

		if($property->type == 'longtext')
			$field->setDefaultRender('textarea');

		return $field;
	}

	public function addRelation($name) {
		$entity = $this->entity;
		$relation = $entity::getDefinition()->relation($name);

		$ids = array(''=>$this->getTranslator()->trans('Choose'));
		foreach($relation['entity']::all() as $v)
			$ids[$v->id] = (string)$v;
				
		if($relation['has'] == 'one') {
			$this->addField(new Fields\SelectField(array(
				'type'	=>	'integer',
				'choices'		=>	$ids,
				'default'	=>	($this->entity->isOld() && $this->entity->$name ? $this->entity->$name->id:null),
			)), $name);
		}
		elseif($relation['has'] == 'many') {
			$this->addField(new Fields\MultipleSelectField(array(
				'type'	=>	'integer',
				'choices'		=>	$ids,
				'default'	=>	($this->entity->isOld() ? $this->entity->$name()->ids():array()),
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
