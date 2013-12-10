<?php
namespace Coxis\Form;

class ModelForm extends Form {
	protected $model;
	protected $i18n = false;

	public function getNewField($model, $name, $properties, $locale=null) {
		$field_params = array();

		$field_params['form'] = $this;

		if($properties->form_hidden)
			$field_params['default'] = '';
		elseif($model->isOld())
			$field_params['default'] = $model->get($name, $locale);

		$field_type = 'Coxis\Form\Fields\TextField';
		if($properties->type == 'boolean')
			$field_type = 'Coxis\Form\Fields\BooleanField';
		elseif($properties->type == 'file') {
			if($properties->multiple)
				$field_type = 'Coxis\Form\Fields\MultipleFileField';
			else
				$field_type = 'Coxis\Form\Fields\FileField';
		}
		elseif($properties->type == 'date')
			$field_type = 'Coxis\Form\Fields\DateField';

		if($properties->in) {
			foreach($properties->in as $v)
				$field_params['choices'][$v] = $v;
			if($properties->multiple)
				$field_type = 'Coxis\Form\Fields\MultipleselectField';
			else
				$field_type = 'Coxis\Form\Fields\SelectField';
		}

		// $field_class = $field_type.'Field';
		$field_class = $field_type;

		$field = new $field_class($field_params);

		if($properties->type == 'longtext')
			$field->setDefaultRender('wysiwyg');

		return $field;
	}

	public function addRelation($name) {
		$model = $this->model;
		$relation = $model::getDefinition()->relations[$name];

		$ids = array(''=>__('Choose'));
		foreach($relation['model']::all() as $v)
			$ids[$v->id] = (string)$v;
				
		if($relation['has'] == 'one') {
			$this->addField(new SelectField(array(
				'type'	=>	'integer',
				'choices'		=>	$ids,
				'default'	=>	($this->model->isOld() && $this->model->$name ? $this->model->$name->id:null),
			)), $name);
		}
		elseif($relation['has'] == 'many') {
			$this->addField(new MultipleSelectField(array(
				'type'	=>	'integer',
				'choices'		=>	$ids,
				'default'	=>	($this->model->isOld() ? $this->model->$name()->ids():array()),
			)), $name);
		}
	}

	function __construct(
		$model, 
		$params=array()
	) {
		$this->model = $model;

		$this->i18n = isset($params['i18n']) && $params['i18n'];
	
		$fields = array();
		foreach($model->properties() as $name=>$properties) {
			if(isset($params['only']) && !in_array($name, $params['only']))
					continue;
			if(isset($params['except']) && in_array($name, $params['except']))
					continue;
			if($properties->editable === false || $properties->form_editable === false)
				continue;

			if($this->i18n && $properties->i18n) {
				$i18ngroup = array();
				foreach(\Config::get('locales') as $locale)
					$i18ngroup[$locale] = $this->getNewField($model, $name, $properties, $locale);
				$fields[$name] = $i18ngroup;
			}
			else
				$fields[$name] = $this->getNewField($model, $name, $properties);
		}

		parent::__construct(
			isset($params['name']) ? $params['name']:$model->getModelName(),
			$params,
			$fields
		);
	}
	
	public function errors($field=null) {
		if(!$this->isSent())
			return array();

		if(!$field)
			$field = $this;

		if(is_subclass_of($field, 'Coxis\Form\AbstractGroup')) {
			if($field instanceof \Coxis\Form\ModelForm)
				$errors = $field->my_errors();
			elseif($field instanceof \Coxis\Form\Form)
				$errors = $field->errors();
				
			foreach($field as $name=>$sub_field) {
				if(is_subclass_of($sub_field, 'Coxis\Form\AbstractGroup')) {
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
	
	public function getModel() {
		return $this->model;
	}
	
	public function my_errors() {
		$data = $this->getData();
		$data = array_filter($data, function($v) {
			return $v !== null; 
		});
		if($this->i18n)
			$this->model->set($data, 'all');
		else
			$this->model->set($data);

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

		return array_merge($errors, parent::my_errors(), $this->model->errors());
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

		if(is_a($group, 'Coxis\Form\ModelForm') || is_subclass_of($group, 'Coxis\Form\ModelForm'))
			$group->model->save();

		if(is_subclass_of($group, 'Coxis\Form\AbstractGroup'))
			foreach($group->fields as $name=>$field)
				if(is_subclass_of($field, 'Coxis\Form\AbstractGroup'))
					$this->_save($field);
	}
}
