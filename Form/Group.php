<?php
namespace Asgard\Form;

/**
 * Group of fieldsor sub-groups.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Group implements GroupInterface {
	/**
	 * Widgets manager.
	 * @var WidgetManagerInterface
	 */
	protected $widgetManager;
	/**
	 * name
	 * @var string
	 */
	protected $name = null;
	/**
	 * Parent.
	 * @var GroupInterface
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
	 * @param GroupInterface  $parent
	 */
	public function __construct(
		array $fields,
		$name       = null,
		array $data = [],
		$parent     = null
		) {
		$this->addFields($fields);
		$this->name = $name;
		$this->data = $data;
		$this->parent = $parent;
	}

	/**
	 * {@inheritDoc}
	 */
	public function createValidator() {
		return $this->parent->createValidator();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTranslator() {
		return $this->parent->getTranslator();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRequest() {
		if($this->parent !== null)
			return $this->parent->getRequest();
		elseif($this->request !== null)
			return $this->request;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getContainer() {
		return $this->parent->getContainer();
	}

	/**
	 * {@inheritDoc}
	 */
	public function name() {
		return $this->name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function size() {
		return count($this->fields);
	}

	/**
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 */
	public function getWidget($widget, $name, $value, array $options=[]) {
		return $this->getWidgetManager()->getWidget($widget, $name, $value, $options, $this);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getWidgetManager() {
		if($this->parent)
			return $this->parent->getWidgetManager();
		elseif($this->widgetManager)
			return $this->widgetManager;
		else
			return $this->widgetManager = new WidgetManager;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setWidgetManager(WidgetManager $WidgetManager) {
		$this->widgetManager = $WidgetManager;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function render($render_callback, $field, array $options=[]) {
		if($this->parent)
			return $this->parent->doRender($render_callback, $field, $options);

		return $this->doRender($render_callback, $field, $options);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isValid() {
		return $this->getValidator()->valid();
	}

	/**
	 * {@inheritDoc}
	 */
	public function sent() {
		return $this->parent->sent();
	}

	/**
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 */
	public function remove($name) {
		unset($this->fields[$name]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get($name) {
		return $this->fields[$name];
	}

	/**
	 * {@inheritDoc}
	 */
	public function add($field, $name=null) {
		if($name !== null)
			$this->fields[$name] = $this->parseFields($field, $name);
		else
			$this->fields[] = $this->parseFields($field, count($this->fields));

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function has($field_name) {
		return isset($this->fields[$field_name]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function resetFields() {
		$this->fields = [];
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function fields() {
		return $this->fields;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addFields(array $fields) {
		foreach($fields as $name=>$sub_fields)
			$this->fields[$name] = $this->parseFields($sub_fields, $name);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset() {
		$this->setData([]);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setData(array $data) {
		$this->data = $data;
		$this->updateChilds();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function data() {
		$res = [];

		foreach($this->fields as $field) {
			if($field instanceof Field)
				$res[$field->shortName()] = $field->value();
			elseif($field instanceof self)
				$res[$field->name()] = $field->data();
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
	 * {@inheritDoc}
	 */
	public function setParent(GroupInterface $parent) {
		$this->parent = $parent;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTopForm() {
		if($this->parent)
			return $this->parent->getTopForm();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setFields(array $fields) {
		$this->fields = [];
		$this->addFields($fields);
	}

	/**
	 * {@inheritDoc}
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
	 * @return \Asgard\Validation\ValidatorInterface
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
		$validator->attributes($constrains);
		$validator->attributesMessages($messages);
		return $validator;
	}

	/**
	 * Do render a field.
	 * @param  string|callable $render_callback
	 * @param  Group|Field     $field
	 * @param  array           $options
	 * @return Widget
	 */
	protected function doRender($render_callback, $field, array &$options) {
		$name = $field->name();
		if($field instanceof Field) {
			$options['field'] = $field;
			$options = $field->options+$options;
			$options['id'] = $field->getID();
			$value = $field->value();
		}
		elseif($field instanceof GroupInterface) {
			$options['group'] = $field;
			$value = null;
		}
		else
			throw new \Exception('Invalid field type.');

		$widget = $this->getWidget($render_callback, $name, $value, $options);
		if($widget === null)
			throw new \Exception('Invalid widget name: '.$render_callback);

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
	 * @param  array|Field|GroupInterface $fields
	 * @param  string $name
	 * @return GroupInterface|Field
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
	 * {@inheritDoc}
	 */
	public function doSave() {
	}

	/**
	 * Save the group and its children.
	 * @param  GroupInterface $group
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