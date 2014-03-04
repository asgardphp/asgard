<?php
namespace Asgard\Form;

class EntityForm extends Form {
	protected $entity;
	protected $i18n = false;

	function __construct(
		$entity, 
		$params=array()
	) {
		$this->entity = $entity;

		$this->i18n = isset($params['i18n']) && $params['i18n'];
	
		$fields = array();
		foreach($entity->properties() as $name=>$properties) {
			if(isset($params['only']) && !in_array($name, $params['only']))
					continue;
			if(isset($params['except']) && in_array($name, $params['except']))
					continue;
			if($properties->editable === false || $properties->form_editable === false)
				continue;

			if($this->i18n && $properties->i18n) {
				$i18ngroup = array();
				foreach(\Config::get('locales') as $locale)
					$i18ngroup[$locale] = $this->getNewField($entity, $name, $properties, $locale);
				$fields[$name] = $i18ngroup;
			}
			else
				$fields[$name] = $this->getNewField($entity, $name, $properties);
		}

		parent::__construct(
			isset($params['name']) ? $params['name']:$entity->getEntityName(),
			$params,
			$fields
		);
	}

	public function getNewField($entity, $name, $properties, $locale=null) {
		$field_params = array();

		$field_params['form'] = $this;

		if($properties->form_hidden)
			$field_params['default'] = '';
		elseif($entity->isOld())
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

		return $field;
	}

	public function addRelation($name) {
		$entity = $this->entity;
		$relation = $entity::getDefinition()->relations[$name];

		$ids = array(''=>__('Choose'));
		foreach($relation['Entity']::all() as $v)
			$ids[$v->id] = (string)$v;
				
		if($relation['has'] == 'one') {
			$this->addField(new SelectField(array(
				'type'	=>	'integer',
				'choices'		=>	$ids,
				'default'	=>	($this->entity->isOld() && $this->entity->$name ? $this->entity->$name->id:null),
			)), $name);
		}
		elseif($relation['has'] == 'many') {
			$this->addField(new MultipleSelectField(array(
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

		if(is_subclass_of($field, 'Asgard\Form\AbstractGroup')) {
			if($field instanceof \Asgard\Form\EntityForm)
				$errors = $field->my_errors();
			elseif($field instanceof \Asgard\Form\Form)
				$errors = $field->errors();
				
			foreach($field as $name=>$sub_field) {
				if(is_subclass_of($sub_field, 'Asgard\Form\AbstractGroup')) {
					$field_errors = $this->errors($sub_field);
					if(sizeof($field_errors) > 0)
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
	
	public function my_errors() {
		$data = $this->getData();
		$data = array_filter($data, function($v) {
			return $v !== null; 
		});
		if($this->i18n)
			$this->entity->set($data, 'all');
		else
			$this->entity->set($data);

		$errors = array();
		foreach($this->files as $name=>$f) {
			switch($f['error']) {
				case UPLOAD_ERR_INI_SIZE:
					$errors[$name][] = __('The uploaded file exceeds the max filesize.');
					break;
				case UPLOAD_ERR_FORM_SIZE:
					$errors[$name][] = __('The uploaded file exceeds the max filesize.');
					break;
				case UPLOAD_ERR_PARTIAL:
					$errors[$name][] = __('The uploaded file was only partially uploaded.');
					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$errors[$name][] = __('Missing a temporary folder.');
					break;
				case UPLOAD_ERR_CANT_WRITE:
					$errors[$name][] = __('Failed to write file to disk.');
					break;
				case UPLOAD_ERR_EXTENSION:
					$errors[$name][] = __('A PHP extension stopped the file upload.');
					break;
			}
		}

		return array_merge($errors, parent::my_errors(), $this->entity->errors());
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
	
	public function _save($group=null) {
		if(!$group)
			$group = $this;

		if(is_a($group, 'Asgard\Form\EntityForm') || is_subclass_of($group, 'Asgard\Form\EntityForm'))
			$group->entity->save();

		if(is_subclass_of($group, 'Asgard\Form\AbstractGroup')) {
			foreach($group->fields as $name=>$field) {
				if(is_subclass_of($field, 'Asgard\Form\AbstractGroup'))
					$this->_save($field);
			}
		}
	}
}
