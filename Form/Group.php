<?php
namespace Asgard\Form;

class Group extends \Asgard\Hook\Hookable implements \ArrayAccess, \Iterator {
	protected $groupName = null;
	protected $dad;
	protected $data = [];
	protected $fields = [];
	protected $errors = [];
	protected $hasfile;
	protected $request;
	protected $widgetsManager;

	public function __construct(
		array $fields,
		$dad=null,
		$name=null,
		$data=null
		) {
		$this->addFields($fields);
		$this->dad = $dad;
		$this->groupName = $name;
		$this->data = $data;
	}

	public function getTranslator() {
		return $this->dad->getTranslator();
	}

	public function getWidget($class, $name, $value, $options) {
		$reflector = new \ReflectionClass($class);
		return $reflector->newInstanceArgs([$name, $value, $options, $this]);
	}

	public function getWidgetsManager() {
		if($this->dad)
			return $this->dad->getWidgetsManager();
		elseif($this->widgetsManager)
			return $this->widgetsManager;
		else
			return $this->widgetsManager = new WidgetsManager;
	}

	public function setWidgetsManager($wm) {
		$this->widgetsManager = $wm;
		return $this;
	}

	protected function doRender($render_callback, $field, &$options) {
		if(!is_string($render_callback) && is_callable($render_callback))
			$cb = $render_callback;
		else
			$cb = $this->getWidgetsManager()->getWidget($render_callback);

		if($cb === null)
			throw new \Exception('Invalid widget name: '.$render_callback);

		if($field instanceof \Asgard\Form\Field) {
			$options['field'] = $field;
			$options = $field->options+$options;
			$options['id'] = $field->getID();
		}
		elseif($field instanceof \Asgard\Form\Group)
			$options['group'] = $field;

		if(is_callable($cb))
			$widget = $cb($field, $options);
		elseif($field instanceof \Asgard\Form\Field)
			$widget = $this->getWidget($cb, $field->getName(), $field->getValue(), $options);
		elseif($field instanceof \Asgard\Form\Group)
			$widget = $this->getWidget($cb, $field->getName(), null, $options);
		else
			throw new \Exception('Invalid widget.');

		if($widget instanceof \Asgard\Form\Widget) {
			if($field instanceof \Asgard\Form\Field)
				$widget->field = $field;
			elseif($field instanceof \Asgard\Form\Group)
				$widget->group = $field;
		}

		return $widget;
	}

	public function render($render_callback, $field, array $options=[]) {
		if($this->dad)
			return $this->dad->doRender($render_callback, $field, $options);

		return $this->doRender($render_callback, $field, $options);
	}

	protected function setErrors(array $errors) {
		foreach($errors as $name=>$error) {
			if(isset($this->fields[$name]))
				$this->fields[$name]->setErrors($error);
		}
	}

	public function resetFields() {
		$this->fields = [];
		return $this;
	}

	public function getFields() {
		return $this->fields;
	}
	
	public function has($field_name) {
		return isset($this->fields[$field_name]);
	}

	public function getParents() {
		if($this->dad)
			$parents = $this->dad->getParents();
		else
			$parents = [];

		if($this->groupName !== null)
			$parents[] = $this->groupName;

		return $parents;
	}
	
	public function getName() {
		return $this->groupName;
	}

	public function getRequest() {
		if($this->dad !== null)
			return $this->dad->getRequest();
		elseif($this->request !== null)
			return $this->request;
		else
			throw new \Exception('Group and its parents do not have request.');
	}
	
	public function isSent() {
		return $this->dad->isSent();
	}

	protected function parseFields($fields, $name) {
		if(is_array($fields)) {
			return new self($fields, $this, $name, 
				(isset($this->data[$name]) ? $this->data[$name]:[])
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
			$field->setDad($this);
			
			if(isset($this->data[$name]))
				$field->setValue($this->data[$name]);
				
			return $field;
		}
		elseif($fields instanceof Group) {
			$group = $fields;
			$group->setName($name);
			$group->setDad($this);
			$group->setData(
				(isset($this->data[$name]) ? $this->data[$name]:[])
			);
				
			return $group;
		}
	}

	public function size() {
		return count($this->fields);
	}
	
	public function addFields(array $fields) {
		foreach($fields as $name=>$sub_fields)
			$this->fields[$name] = $this->parseFields($sub_fields, $name);
			
		return $this;
	}
	
	public function addField(Field $field, $name=null) {
		$reflect = new \ReflectionClass($this);
		try {
			if($reflect->getProperty($name))
				throw new \Exception('Can\'t use keyword "'.$name.'" for form field');
		} catch(\Exception $e) {}
		if($name !== null)
			$this->fields[$name] = $this->parseFields($field, $name);
		else
			$this->fields[] = $this->parseFields($field, count($this->fields));
		
		return $this;
	}
	
	public function setDad(Group $dad) {
		$this->dad = $dad;
	}

	public function getTopForm() {
		if($this->dad)
			return $this->dad->getTopForm();
		return $this;
	}
	
	public function setFields(array $fields) {
		$this->fields = [];
		$this->addFields($fields, $this);
	}
	
	public function setName($name) {
		$this->groupName = $name;
	}
	
	public function reset() {
		$this->setData([], []);
		
		return $this;
	}
	
	public function setData(array $data) {
		$this->data = $data;
		
		$this->updateChilds();
		
		return $this;
	}
	
	public function getData() {
		$res = [];
		
		foreach($this->fields as $field) {
			if($field instanceof \Asgard\Form\Field)
				$res[$field->name] = $field->getValue();
			elseif($field instanceof \Asgard\Form\Group)
				$res[$field->groupName] = $field->getData();
		}
		
		return $res;
	}
	
	public function hasFile() {
		if($this->hasfile === true)
			return true;
		foreach($this->fields as $name=>$field) {
			if($field instanceof \Asgard\Form\Group) {
				if($field->hasFile())
					return true;
			}
			elseif($field instanceof \Asgard\Form\Fields\FileField)
				return true;
		}
		
		return false;
	}
	
	public function errors() {
		if(!$this->isSent())
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

	public function getApp() {
		return $this->dad->getApp();
	}

	protected function getValidator() {
		$validator = new \Asgard\Validation\Validator;
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
		if($app = $this->getApp()) {
			$validator->setRegistry($app['rulesregistry']);
			$validator->setTranslator($app['translator']);
		}
		$validator->attributes($constrains);
		$validator->attributesMessages($messages);
		return $validator;
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

	public function save() {
		if($errors = $this->errors()) {
			$e = new FormException();
			$e->errors = $errors;
			throw $e;
		}
	
		return $this->_save();
	}
	
	protected function _save($group=null) {
		if(!$group)
			$group = $this;
		$group->trigger('save');
		if($group instanceof \Asgard\Form\Group) {
			foreach($group->fields as $name=>$field) {
				if($field instanceof self)
					$field->_save($field);
			}
		}
	}
	
	public function isValid() {
		return $this->getValidator()->valid();
	}
	
	public function remove($name) {
		unset($this->fields[$name]);
	}

	public function get($name) {
		return $this->fields[$name];
	}

	public function add($name, $field, array $options=[]) {
		$fieldClass = $field.'Field';
		$this->fields[$name] = $this->parseFields(new $fieldClass($options), $name);
	}

	public function trigger($name, array $args=[], $cb=null, &$chain=null) {
		return parent::trigger($name, array_merge([$this], $args), $cb, $chain);
	}
	
	protected function updateChilds() {
		foreach($this->fields as $name=>$field) {
			if($field instanceof \Asgard\Form\Group) {
				$field->setData(
					(isset($this->data[$name]) ? $this->data[$name]:[])
				);
			}
			elseif($field instanceof \Asgard\Form\Field) {
				if(isset($this->data[$name]))
					$field->setValue($this->data[$name]);
				elseif($this->isSent())
					$field->setValue(null);
			}
		}
	}
	
	/* ARRAY */
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
	
	/* ITERATOR */
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
}