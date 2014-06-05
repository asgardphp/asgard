<?php
namespace Asgard\Form;

class Group extends \Asgard\Hook\Hookable implements \ArrayAccess, \Iterator {
	protected $groupName = null;
	protected $dad;
	protected $data = array();
	protected $fields = array();
	protected $errors = array();
	protected $hasfile;
	protected $request;

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

	public function render($render_callback, Field $field, array $options=array()) {
		return $this->dad->render($render_callback, $field, $options);
	}

	protected function setErrors(array $errors) {
		foreach($errors as $name=>$error) {
			if(isset($this->fields[$name]))
				$this->fields[$name]->setErrors($error);
		}
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
			$parents = array();

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
				(isset($this->data[$name]) ? $this->data[$name]:array())
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
				(isset($this->data[$name]) ? $this->data[$name]:array())
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
		$this->fields = array();
		$this->addFields($fields, $this);
	}
	
	public function setName($name) {
		$this->groupName = $name;
	}
	
	public function reset() {
		$this->setData(array(), array());
		
		return $this;
	}
	
	public function setData(array $data) {
		$this->data = $data;
		
		$this->updateChilds();
		
		return $this;
	}
	
	public function getData() {
		$res = array();
		
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
			return array();
		
		$errors = array();
	
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
		$errors = array();
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

	protected function getValidator() {
		$validator = new \Asgard\Validation\Validator;
		$constrains = array();
		$messages = array();
		
		foreach($this->fields as $name=>$field) {
			if($field instanceof Field) {
				if($field_rules = $field->getValidationRules())
					$constrains[$name] = $field_rules;
				if($field_messages = $field->getValidationMessages())
					$messages[$name] = $field_messages;
			}
		}

		$validator->attributes($constrains);
		$validator->attributesMessages($messages);
		return $validator;
	}

	protected function myErrors() {
		$data = $this->data;

		$report = $this->getValidator()->errors($data);

		$errors = array();
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

	public function add($name, $field, array $options=array()) {
		$fieldClass = $field.'Field';
		$this->fields[$name] = $this->parseFields(new $fieldClass($options), $name);
	}

	public function trigger($name, array $args=array(), $cb=null, $print=false) {
		return parent::trigger($name, array_merge(array($this), $args), $cb, $print);
	}
	
	protected function updateChilds() {
		foreach($this->fields as $name=>$field) {
			if($field instanceof \Asgard\Form\Group) {
				$field->setData(
					(isset($this->data[$name]) ? $this->data[$name]:array())
				);
			}
			elseif($field instanceof \Asgard\Form\Field) {
				if(isset($this->data[$name]))
					$field->setValue($this->data[$name]);
				else {
					if($this->isSent()) {
						if(isset($field->params['multiple']) && $field->params['multiple'])
							$field->setValue(array());
						else
							$field->setValue('');
					}
				}
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