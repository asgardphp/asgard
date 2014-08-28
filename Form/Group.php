<?php
namespace Asgard\Form;

class Group implements \ArrayAccess, \Iterator {
	#dependencies
	protected $widgetsManager;

	protected $name = null;
	protected $parent;
	protected $data = [];
	protected $fields = [];
	protected $errors = [];
	protected $hasfile;
	protected $request;

	/* Constructor */
	public function __construct(
		array $fields,
		$name=null,
		$data=null,
		$parent=null
		) {
		$this->addFields($fields);
		$this->name = $name;
		$this->data = $data;
		$this->parent = $parent;
	}

	/* Dependencies */
	public function createValidator() {
		return $this->parent->getTranslator();
	}

	public function getTranslator() {
		return $this->parent->getTranslator();
	}

	public function getRequest() {
		if($this->parent !== null)
			return $this->parent->getRequest();
		elseif($this->request !== null)
			return $this->request;
	}

	public function getContainer() {
		return $this->parent->getContainer();
	}

	/* General */
	public function name() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = $name;
	}

	public function size() {
		return count($this->fields);
	}
	
	public function hasFile() {
		if($this->hasfile === true)
			return true;
		foreach($this->fields as $name=>$field) {
			if($field instanceof self) {
				if($field->hasFile())
					return true;
			}
			elseif($field instanceof Fields\FileField)
				return true;
		}
		
		return false;
	}

	/* Widgets */
	public function getWidget($class, $name, $value, $options) {
		$reflector = new \ReflectionClass($class);
		return $reflector->newInstanceArgs([$name, $value, $options, $this]);
	}

	public function getWidgetsManager() {
		if($this->parent)
			return $this->parent->getWidgetsManager();
		elseif($this->widgetsManager)
			return $this->widgetsManager;
		else
			return $this->widgetsManager = new WidgetsManager;
	}

	public function setWidgetsManager($wm) {
		$this->widgetsManager = $wm;
		return $this;
	}

	/* Rendering */
	public function render($render_callback, $field, array $options=[]) {
		if($this->parent)
			return $this->parent->doRender($render_callback, $field, $options);

		return $this->doRender($render_callback, $field, $options);
	}

	/* Save & Validation */
	public function isValid() {
		return $this->getValidator()->valid();
	}

	public function sent() {
		return $this->parent->sent();
	}
	
	public function errors() {
		if(!$this->sent())
			return [];
		
		$errors = [];
	
		foreach($this->fields as $name=>$field) {
			if($field instanceof self) {
				$errors[$name] = $field->errors();
				if(count($errors[$name]) === 0)
					unset($errors[$name]);
			}
		}

		$this->errors = $errors + $this->myErrors();

		$this->setErrors($this->errors);

		return $this->errors;
	}

	/* Fields */
	public function remove($name) {
		unset($this->fields[$name]);
	}

	public function get($name) {
		return $this->fields[$name];
	}
	
	public function add(Field $field, $name=null) {
		if($name !== null)
			$this->fields[$name] = $this->parseFields($field, $name);
		else
			$this->fields[] = $this->parseFields($field, count($this->fields));
		
		return $this;
	}
	
	public function has($field_name) {
		return isset($this->fields[$field_name]);
	}

	public function resetFields() {
		$this->fields = [];
		return $this;
	}

	public function fields() {
		return $this->fields;
	}
	
	public function addFields(array $fields) {
		foreach($fields as $name=>$sub_fields)
			$this->fields[$name] = $this->parseFields($sub_fields, $name);
			
		return $this;
	}
	
	/* Data */
	public function reset() {
		$this->setData([]);
		
		return $this;
	}
	
	public function setData(array $data) {
		$this->data = $data;
		
		$this->updateChilds();
		
		return $this;
	}
	
	public function data() {
		$res = [];
		
		foreach($this->fields as $field) {
			if($field instanceof Field)
				$res[$field->name] = $field->value();
			elseif($field instanceof self)
				$res[$field->name] = $field->data();
		}
		
		return $res;
	}
	
	/* Array */
	public function offsetSet($offset, $value) {
		if(is_null($offset))
			$this->fields[] = $this->parseFields($value, count($this->fields));
		else
			$this->fields[$offset] = $this->parseFields($value, $offset);
	}
	
	public function offsetExists($offset) {
		return isset($this->fields[$offset]);
	}
	
	public function offsetUnset($offset) {
		unset($this->fields[$offset]);
	}
	
	public function offsetGet($offset) {
		return isset($this->fields[$offset]) ? $this->fields[$offset] : null;
	}
	
	/* Iterator */
	public function valid() {
		$key = key($this->fields);
		return $key !== NULL && $key !== FALSE;
	}

	public function rewind() {
		reset($this->fields);
	}

	public function current() {
		return current($this->fields);
	}

	public function key()  {
		return key($this->fields);
	}

	public function next()  {
		return next($this->fields);
	}

	/* Internal */
	public function setParent(Group $parent) {
		$this->parent = $parent;
	}

	public function getTopForm() {
		if($this->parent)
			return $this->parent->getTopForm();
		return $this;
	}
	
	public function setFields(array $fields) {
		$this->fields = [];
		$this->addFields($fields);
	}

	public function getParents() {
		if($this->parent)
			$parents = $this->parent->getParents();
		else
			$parents = [];

		if($this->name !== null)
			$parents[] = $this->name;

		return $parents;
	}

	protected function getValidator() {
		$validator = $this->createValidator();
		$constrains = [];
		$messages = [];
		
		foreach($this->fields as $name=>$field) {
			if($field instanceof Field) {
				if($field_rules = $field->getValidationRules())
					$constrains[$name] = $field_rules;
				if($field_messages = $field->getValidationMessages())
					$messages[$name] = $field_messages;
			}
		}

		$validator->set('group', $this);
		if($container = $this->getContainer()) {
			$validator->setRegistry($container['rulesregistry']);
			$validator->setTranslator($container['translator']);
		}
		$validator->attributes($constrains);
		$validator->attributesMessages($messages);
		return $validator;
	}

	protected function doRender($render_callback, $field, &$options) {
		if(!is_string($render_callback) && is_callable($render_callback))
			$cb = $render_callback;
		else
			$cb = $this->getWidgetsManager()->getWidget($render_callback);

		if($cb === null)
			throw new \Exception('Invalid widget name: '.$render_callback);

		if($field instanceof Field) {
			$options['field'] = $field;
			$options = $field->options+$options;
			$options['id'] = $field->getID();
		}
		elseif($field instanceof self)
			$options['group'] = $field;

		if(is_callable($cb))
			$widget = $cb($field, $options);
		elseif($field instanceof Field)
			$widget = $this->getWidget($cb, $field->name(), $field->value(), $options);
		elseif($field instanceof self)
			$widget = $this->getWidget($cb, $field->name(), null, $options);
		else
			throw new \Exception('Invalid widget.');

		if($widget instanceof Widget) {
			if($field instanceof Field)
				$widget->field = $field;
			elseif($field instanceof self)
				$widget->group = $field;
		}

		return $widget;
	}

	protected function setErrors(array $errors) {
		foreach($errors as $name=>$error) {
			if(isset($this->fields[$name]))
				$this->fields[$name]->setErrors($error);
		}
	}

	protected function parseFields($fields, $name) {
		if(is_array($fields)) {
			return new self(
				$fields,
				$name,
				(isset($this->data[$name]) ? $this->data[$name]:[]),
				$this
			);
		}
		elseif($fields instanceof Field) {
			$reflect = new \ReflectionClass($this);
			try {
				if($reflect->getProperty($name))
					throw new \Exception('Can\'t use keyword "'.$name.'" for form field');
			} catch(\Exception $e) {}
			$field = $fields;
			$field->setName($name);
			$field->setParent($this);
			
			if(isset($this->data[$name]))
				$field->setValue($this->data[$name]);
			
			return $field;
		}
		elseif($fields instanceof self) {
			$group = $fields;
			$group->setName($name);
			$group->setParent($this);
			$group->setData(
				(isset($this->data[$name]) ? $this->data[$name]:[])
			);
				
			return $group;
		}
	}

	public function doSave() {
	}
	
	protected function _save($group=null) {
		if(!$group)
			$group = $this;

		$group->doSave();

		if($group instanceof self) {
			foreach($group->fields as $name=>$field) {
				if($field instanceof self)
					$field->_save($field);
			}
		}
	}
	
	protected function updateChilds() {
		foreach($this->fields as $name=>$field) {
			if($field instanceof self) {
				$field->setData(
					(isset($this->data[$name]) ? $this->data[$name]:[])
				);
			}
			elseif($field instanceof Field) {
				if(isset($this->data[$name]))
					$field->setValue($this->data[$name]);
				elseif($this->sent())
					$field->setValue(null);
			}
		}
	}

	protected function myErrors() {
		$data = $this->data;

		$report = $this->getValidator()->errors($data);

		$errors = [];
		foreach($this->fields as $name=>$field) {
			if($field instanceof Fields\FileField && isset($this->data[$name])) {
				$f = $this->data[$name];
				switch($f->error()) {
					case UPLOAD_ERR_INI_SIZE:
						$errors[$name][] = $this->getTranslator()->trans('The uploaded file exceeds the max filesize.');
						break;
					case UPLOAD_ERR_FORM_SIZE:
						$errors[$name][] = $this->getTranslator()->trans('The uploaded file exceeds the max filesize.');
						break;
					case UPLOAD_ERR_PARTIAL:
						$errors[$name][] = $this->getTranslator()->trans('The uploaded file was only partially uploaded.');
						break;
					case UPLOAD_ERR_NO_TMP_DIR:
						$errors[$name][] = $this->getTranslator()->trans('Missing a temporary folder.');
						break;
					case UPLOAD_ERR_CANT_WRITE:
						$errors[$name][] = $this->getTranslator()->trans('Failed to write file to disk.');
						break;
					case UPLOAD_ERR_EXTENSION:
						$errors[$name][] = $this->getTranslator()->trans('A PHP extension stopped the file upload.');
						break;
				}
			}
		}

		return array_merge($errors, $this->getReportErrors($report));
	}
	
	protected function getReportErrors(\Asgard\Validation\Report $report) {
		$errors = [];
		if($report->attributes()) {
			foreach($report->attributes() as $attribute=>$attrReport) {
				$attrErrors = $this->getReportErrors($attrReport);
				if($attrErrors)
					$errors[$attribute] = $attrErrors;
			}	
		}
		else
			return $report->errors();
		return $errors;
	}
}