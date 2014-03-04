<?php
namespace Asgard\Form;

abstract class AbstractGroup extends \Asgard\Hook\Hookable implements \ArrayAccess, \Iterator {
	protected $groupName = null;
	protected $dad;
	public $data = array();
	public $files = array();
	protected $fields = array();
	public $errors = array();
	public $hasfile;

	public function render($render_callback, $field, $options=array()) {
		return $this->dad->render($render_callback, $field, $options);
	}

	public function setErrors($errors) {
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
		$parents = array();
		
		if($this->groupName !== null)
			$parents[] = $this->groupName;
			
		if($this->dad)
			$parents = array_merge($this->dad->getParents(), $parents);
			
		return $parents;
	}
	
	public function getName() {
		return $this->groupName;
	}
	
	public function isSent() {
		$method = strtolower(\Asgard\Core\App::get('request')->method());
		if($this->dad)
			return $this->dad->isSent();
		else {
			if($this->groupName) {
				if($method == 'post' || $method == 'put')
					return \Asgard\Core\App::get('post')->has($this->groupName);
				elseif($method == 'get')
					return \Asgard\Core\App::get('post')->has($this->groupName);
				else
					return false;
			}
			else
				return false;
		}
	}

	public function parseFields($fields, $name) {
			if(is_array($fields)) {
				return new Group($fields, $this, $name, 
					(isset($this->data[$name]) ? $this->data[$name]:array()), 
					(isset($this->files[$name]) ? $this->files[$name]:array())
				);
			}
			elseif(is_object($fields) && is_subclass_of($fields, 'Asgard\Form\Fields\Field')) {
				if(in_array($name, array('groupName', 'dad', 'data', 'fields', 'params', 'files'), true))
					throw new \Exception('Can\'t use keyword "'.$name.'" for form field');
				$field = $fields;
				$field->setName($name);
				$field->setDad($this);
				
				if(isset($this->data[$name]))
					$field->setValue($this->data[$name]);
				elseif(isset($this->files[$name]))
					$field->setValue($this->files[$name]);
					
				return $field;
			}
			elseif($fields instanceof \Asgard\Form\AbstractGroup) {
				$form = $fields;
				$form->setName($name);
				$form->setDad($this);
				$form->setData(
					(isset($this->data[$name]) ? $this->data[$name]:array()),
					(isset($this->files[$name]) ? $this->files[$name]:array())
				);
					
				return $form;
			}
	}

	public function size() {
		return sizeof($this->fields);
	}
	
	public function addFields($fields) {
		foreach($fields as $name=>$sub_fields)
			$this->fields[$name] = $this->parseFields($sub_fields, $name);
			
		return $this;
	}
	
	public function addField($field, $name=null) {
		if(in_array($name, array('groupName', 'dad', 'data', 'fields', 'params'), true))
			throw new \Exception('Can\'t use keyword "'.$name.'"" for form field');
		if($name !== null)
			$this->fields[$name] = $this->parseFields($field, $name);
		else
			$this->fields[] = $this->parseFields($field, sizeof($this->fields));
		
		return $this;
	}
	
	public function setDad($dad) {
		$this->dad = $dad;
	}
	
	public function setFields($fields) {
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
	
	public function setData($data, $files) {
		$this->data = $data;
		$this->files = $files;
		
		$this->updateChilds();
		
		return $this;
	}
	
	public function getData() {
		$res = array();
		
		foreach($this->fields as $field)
			if($field instanceof \Asgard\Form\Fields\Field)
				$res[$field->name] = $field->getValue();
			elseif($field instanceof \Asgard\Form\Group)
				$res[$field->groupName] = $field->getData();
		
		return $res;
	}
	
	public function hasFile() {
		if($this->hasfile === true)
			return true;
		foreach($this->fields as $name=>$field) {
			if(is_subclass_of($field, 'Asgard\Form\AbstractGroup')) {
				if($field->hasFile())
					return true;
			}
			elseif($field instanceof \Asgard\Form\Fields\FileField)
				return true;
		}
		
		return false;
	}
	
	protected function updateChilds() {
		foreach($this->fields as $name=>$field) {
			if($field instanceof \Asgard\Form\AbstractGroup) {
				$field->setData(
					(isset($this->data[$name]) ? $this->data[$name]:array()),
					(isset($this->files[$name]) ? $this->files[$name]:array())
				);
			}
			elseif($field instanceof \Asgard\Form\Fields\Field) {
				if($field instanceof \Asgard\Form\Fields\FileField) {
					if(isset($this->files[$name]))
						$field->setValue($this->files[$name]);
				}
				elseif(isset($this->data[$name]))
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
	
	public function errors() {
		if(!$this->isSent())
			return array();
		
		$errors = array();
	
		foreach($this->fields as $name=>$field)
			if($field instanceof \Asgard\Form\AbstractGroup) {
				$errors[$name] = $field->errors();
				if(sizeof($errors[$name]) == 0)
					unset($errors[$name]);
			}

		$this->errors = $errors + $this->my_errors();

		$this->setErrors($this->errors);

		return $this->errors;
	}
	
	public function my_errors() {
		$validator = new \Asgard\Validation\Validator();
		$constrains = array();
		$messages = array();
		
		foreach($this->fields as $name=>$field) {
			if(is_subclass_of($field, 'Asgard\Form\Fields\Field')) {
				if($field_rules = $field->getValidationRules())
					$constrains[$name] = $field_rules;
				if($field_messages = $field->getValidationMessages())
					$constrains[$name] = $field_messages;
			}
		}

		$validator->setConstrains($constrains);
		$validator->setMessages($messages);

		$data = $this->data + $this->files;

		return $validator->errors($data);
	}

	public function addErrors($errors) {
		$this->errors = array_merge($this->errors, $errors);
	}
	
	public function save() {
		if($errors = $this->errors()) {
			$e = new FormException();
			$e->errors = $errors;
			throw $e;
		}
	
		return $this->_save();
	}
	
	public function _save($group=null) {
		if(!$group)
			$group = $this;
			
		if($group instanceof \Asgard\Form\AbstractGroup)
			foreach($group->fields as $name=>$field)
				if($field instanceof \Asgard\Form\AbstractGroup)
					$field->_save($field);
	}
	
	public function isValid() {
		return !$this->errors();
	}
	
	public function remove($name) {
		unset($this->fields[$name]);
	}
	
	public function __unset($name) {
		$this->remove($name);
	}

	public function get($name) {
		return $this->fields[$name];
	}

	public function add($name, $field, $options=array()) {
		$fieldClass = $field.'Field';
		$this->__set($name, new $fieldClass($options));
	}
	
	public function __get($name) {
		return $this->get($name);
	}
	
	public function __set($k, $v) {
		$this->fields[$k] = $this->parseFields($v, $k);
		
		return $this;
	}

	public function __isset($name) {
		return isset($this->fields[$name]);
	}
	
	/* IMPLEMENTS */
	
    public function offsetSet($offset, $value) {
		if(is_null($offset))
			$this->fields[] = $this->parseFields($value, sizeof($this->fields));
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
	
    public function valid() {
		$key = key($this->fields);
		$var = ($key !== NULL && $key !== FALSE);
		return $var;
    }

	public function trigger($name, $args=array(), $cb=null, $print=false) {
		return parent::trigger($name, array_merge(array($this), $args), $cb, $print);
	}
}