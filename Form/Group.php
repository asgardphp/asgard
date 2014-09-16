<?php
namespace Asgard\Form;

/**
 * Group of fieldsor sub-groups.
 */
class Group implements \ArrayAccess, \Iterator {
	/**
	 * Widgets manager.
	 * @var WidgetsManager
	 */
	protected $widgetsManager;
	/**
	 * name
	 * @var string
	 */
	protected $name = null;
	/**
	 * Parent.
	 * @var Group
	 */
	protected $parent;
	/**
	 * Data.
	 * @var array
	 */
	protected $data = [];
	/**
	 * Fields.
	 * @var array
	 */
	protected $fields = [];
	/**
	 * Errors.
	 * @var array
	 */
	protected $errors = [];
	/**
	 * Has file flag.
	 * @var boolean
	 */
	protected $hasfile;
	/**
	 * Request.
	 * @var \Asgard\Http\Request
	 */
	protected $request;

	/**
	 * Constructor.
	 * @param array  $fields
	 * @param string $name
	 * @param array  $data
	 * @param Group  $parent
	 */
	public function __construct(
		array $fields,
		$name=null,
		array $data=[],
		$parent=null
		) {
		$this->addFields($fields);
		$this->name = $name;
		$this->data = $data;
		$this->parent = $parent;
	}

	/**
	 * Create a validator.
	 * @return \Asgard\Validation\Validator
	 */
	public function createValidator() {
		return $this->parent->getTranslator();
	}

	/**
	 * Get the translator.
	 * @return \Symfony\Component\Translation\TranslatorInterface
	 */
	public function getTranslator() {
		return $this->parent->getTranslator();
	}

	/**
	 * Get the request from group or a parent.
	 * @return \Asgard\Http\Request
	 */
	public function getRequest() {
		if($this->parent !== null)
			return $this->parent->getRequest();
		elseif($this->request !== null)
			return $this->request;
	}

	/**
	 * Get the parent container.
	 * @return \Asgard\Container\Container
	 */
	public function getContainer() {
		return $this->parent->getContainer();
	}

	/**
	 * Return the name.
	 * @return string
	 */
	public function name() {
		return $this->name;
	}
	
	/**
	 * Set the name.
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Return the number of fields.
	 * @return integer
	 */
	public function size() {
		return count($this->fields);
	}
	
	/**
	 * Check if group has a file.
	 * @return boolean true if has file
	 */
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

	/**
	 * Get a widget instance.
	 * @param  string $class
	 * @param  string $name
	 * @param  mixed  $value
	 * @param  array  $options
	 * @return Widget
	 */
	public function getWidget($class, $name, $value, array $options) {
		$reflector = new \ReflectionClass($class);
		return $reflector->newInstanceArgs([$name, $value, $options, $this]);
	}

	/**
	 * Return the widgets manager.
	 * @return WidgetsManager
	 */
	public function getWidgetsManager() {
		if($this->parent)
			return $this->parent->getWidgetsManager();
		elseif($this->widgetsManager)
			return $this->widgetsManager;
		else
			return $this->widgetsManager = new WidgetsManager;
	}

	/**
	 * Set the widgets manager.
	 * @param WidgetsManager $wm
	 */
	public function setWidgetsManager(WidgetsManager $widgetsManager) {
		$this->widgetsManager = $widgetsManager;
		return $this;
	}

	/**
	 * Render a field.
	 * @param  string|callable $render_callback
	 * @param  Field           $field
	 * @param  array           $options
	 * @return string|Widget
	 */
	public function render($render_callback, $field, array $options=[]) {
		if($this->parent)
			return $this->parent->doRender($render_callback, $field, $options);

		return $this->doRender($render_callback, $field, $options);
	}

	/**
	 * Check if group is valid.
	 * @return boolean true if valid
	 */
	public function isValid() {
		return $this->getValidator()->valid();
	}

	/**
	 * Check if group's form was sent.
	 * @return boolean true if sent
	 */
	public function sent() {
		return $this->parent->sent();
	}
	
	/**
	 * Return errors.
	 * @return array
	 */
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

	/**
	 * Remove a field.
	 * @param  string $name
	 */
	public function remove($name) {
		unset($this->fields[$name]);
	}

	/**
	 * Return a field.
	 * @param  string $name
	 * @return Field|Group
	 */
	public function get($name) {
		return $this->fields[$name];
	}
	
	/**
	 * Add a field.
	 * @param Field|Group  $field
	 * @param string       $name
	 */
	public function add($field, $name=null) {
		if($name !== null)
			$this->fields[$name] = $this->parseFields($field, $name);
		else
			$this->fields[] = $this->parseFields($field, count($this->fields));
		
		return $this;
	}
	
	/**
	 * Check if has a field.
	 * @param  string  $field_name
	 * @return boolean
	 */
	public function has($field_name) {
		return isset($this->fields[$field_name]);
	}

	/**
	 * Reset fields.
	 * @return Group $this
	 */
	public function resetFields() {
		$this->fields = [];
		return $this;
	}

	/**
	 * Return all fields.
	 * @return array
	 */
	public function fields() {
		return $this->fields;
	}
	
	/**
	 * Add fields.
	 * @param array $fields
	 */
	public function addFields(array $fields) {
		foreach($fields as $name=>$sub_fields)
			$this->fields[$name] = $this->parseFields($sub_fields, $name);
		return $this;
	}
	
	/**
	 * Reset data.
	 * @return Group $this
	 */
	public function reset() {
		$this->setData([]);
		return $this;
	}
	
	/**
	 * Set data.
	 * @param array $data
	 */
	public function setData(array $data) {
		$this->data = $data;
		$this->updateChilds();
		return $this;
	}
	
	/**
	 * Return data.
	 * @return array
	 */
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
	
	/**
	 * Array set implementation.
	 * @param  string $offset
	 * @param  mixed $value
	 */
	public function offsetSet($offset, $value) {
		if(is_null($offset))
			$this->fields[] = $this->parseFields($value, count($this->fields));
		else
			$this->fields[$offset] = $this->parseFields($value, $offset);
	}
	
	/**
	 * Array exists implementation.
	 * @param  string $offset
	 * @return boolean
	 */
	public function offsetExists($offset) {
		return isset($this->fields[$offset]);
	}
	
	/**
	 * Array unset implementation.
	 * @param  string $offset
	 */
	public function offsetUnset($offset) {
		unset($this->fields[$offset]);
	}
	
	/**
	 * Array get implementation.
	 * @param  string $offset
	 * @return mixed
	 */
	public function offsetGet($offset) {
		return isset($this->fields[$offset]) ? $this->fields[$offset] : null;
	}
	
	/**
	 * Iterator valid implementation.
	 * @return boolean
	 */
	public function valid() {
		$key = key($this->fields);
		return $key !== NULL && $key !== FALSE;
	}

	/**
	 * Iterator rewind implementation.
	 */
	public function rewind() {
		reset($this->fields);
	}

	/**
	 * Iterator current implementation.
	 * @return integer
	 */
	public function current() {
		return current($this->fields);
	}

	/**
	 * Iterator key implementation.
	 * @return string
	 */
	public function key()  {
		return key($this->fields);
	}

	/**
	 * Iterator next implementation.
	 * @return mixed
	 */
	public function next()  {
		return next($this->fields);
	}

	/**
	 * Set parent.
	 * @param  Group $parent
	 * @return Group $thi
	 */
	public function setParent(Group $parent) {
		$this->parent = $parent;
		return $this;
	}

	/**
	 * Get top parent form.
	 * @return Group
	 */
	public function getTopForm() {
		if($this->parent)
			return $this->parent->getTopForm();
		return $this;
	}
	
	/**
	 * Set fields.
	 * @param array $fields
	 */
	public function setFields(array $fields) {
		$this->fields = [];
		$this->addFields($fields);
	}

	/**
	 * Get all parents.
	 * @return array
	 */
	public function getParents() {
		if($this->parent)
			$parents = $this->parent->getParents();
		else
			$parents = [];

		if($this->name !== null)
			$parents[] = $this->name;

		return $parents;
	}

	/**
	 * Return a validator.
	 * @return \Asgard\Validation\Validator
	 */
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

	/**
	 * Do render a field.
	 * @param  string|callable $render_callback
	 * @param  Group|Field     $field
	 * @param  array           $options
	 * @return string|Widget
	 */
	protected function doRender($render_callback, $field, array &$options) {
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

	/**
	 * Set errors.
	 * @param array $errors
	 */
	protected function setErrors(array $errors) {
		foreach($errors as $name=>$error) {
			if(isset($this->fields[$name]))
				$this->fields[$name]->setErrors($error);
		}
	}

	/**
	 * Parse new fields.
	 * @param  array|Field|Group $fields
	 * @param  string $name
	 * @return Group|Field
	 */
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

	/**
	 * Actually perform the group saving. Empty by default but can be overriden.
	 */
	public function doSave() {
	}
	
	/**
	 * Save the group and its children.
	 * @param  Group $group
	 */
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
	
	/**
	 * Update children data.
	 */
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

	/**
	 * Return the group own errors.
	 * @return array
	 */
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
	
	/**
	 * Return array of errors from a report.
	 * @param  \Asgard\Validation\Report $report
	 * @return array
	 */
	protected function getReportErrors(\Asgard\Validation\Report $report) {
		if($report->attributes()) {
			$errors = [];
			foreach($report->attributes() as $attribute=>$attrReport) {
				$attrErrors = $this->getReportErrors($attrReport);
				if($attrErrors)
					$errors[$attribute] = $attrErrors;
			}	
			return $errors;
		}
		else
			return $report->errors();
	}
}