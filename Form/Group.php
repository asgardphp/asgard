<?php
namespace Asgard\Form;

class Group extends \Asgard\Hook\Hookable implements \ArrayAccess, \Iterator {
	protected $_groupName = null;
	protected $_dad;
	protected $_data = array();
	protected $_fields = array();
	protected $_errors = array();
	protected $_hasfile;
	protected $_request;

	public function __construct(
		array $fields,
		$dad=null,
		$name=null,
		$data=null,
		\Asgard\Hook\Hook $hook,
		\Symfony\Component\Translation\TranslatorInterface $translator
		) {
		$this->addFields($fields);
		$this->_dad = $dad;
		$this->_groupName = $name;
		$this->_data = $data;
	}

	public function getTranslator() {
		return $this->_dad->getTranslator();
	}

	public function render($render_callback, Field $field, array $options=array()) {
		return $this->_dad->render($render_callback, $field, $options);
	}

	protected function setErrors(array $errors) {
		foreach($errors as $name=>$error) {
			if(isset($this->_fields[$name]))
				$this->_fields[$name]->setErrors($error);
		}
	}

	public function getFields() {
		return $this->_fields;
	}
	
	public function has($field_name) {
		return isset($this->_fields[$field_name]);
	}

	public function getParents() {
		if($this->_dad)
			$parents = $this->_dad->getParents();
		else
			$parents = array();

		if($this->_groupName !== null)
			$parents[] = $this->_groupName;

		return $parents;
	}
	
	public function getName() {
		return $this->_groupName;
	}

	public function getRequest() {
		if($this->_dad !== null)
			return $this->_dad->getRequest();
		elseif($this->_request !== null)
			return $this->_request;
		else
			return $this->_request = \Asgard\Http\Request::createFromGlobals();
	}
	
	public function isSent() {
		return $this->_dad->isSent();
	}

	protected function parseFields($fields, $name) {
			if(is_array($fields)) {
				return new self($fields, $this, $name, 
					(isset($this->_data[$name]) ? $this->_data[$name]:array())
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
				
				if(isset($this->_data[$name]))
					$field->setValue($this->_data[$name]);
					
				return $field;
			}
			elseif($fields instanceof Group) {
				$group = $fields;
				$group->setName($name);
				$group->setDad($this);
				$group->setData(
					(isset($this->_data[$name]) ? $this->_data[$name]:array())
				);
					
				return $group;
			}
	}

	public function size() {
		return count($this->_fields);
	}
	
	public function addFields(array $fields) {
		foreach($fields as $name=>$sub_fields)
			$this->_fields[$name] = $this->parseFields($sub_fields, $name);
			
		return $this;
	}
	
	public function addField(Field $field, $name=null) {
		$reflect = new \ReflectionClass($this);
		try {
			if($reflect->getProperty($name))
				throw new \Exception('Can\'t use keyword "'.$name.'" for form field');
		} catch(\Exception $e) {}
		if($name !== null)
			$this->_fields[$name] = $this->parseFields($field, $name);
		else
			$this->_fields[] = $this->parseFields($field, count($this->fields));
		
		return $this;
	}
	
	public function setDad(Group $dad) {
		$this->_dad = $dad;
	}

	public function getTopForm() {
		if($this->_dad)
			return $this->_dad->getTopForm();
		return $this;
	}
	
	public function setFields(array $fields) {
		$this->_fields = array();
		$this->addFields($fields, $this);
	}
	
	public function setName($name) {
		$this->_groupName = $name;
	}
	
	public function reset() {
		$this->setData(array(), array());
		
		return $this;
	}
	
	public function setData(array $data) {
		$this->_data = $data;
		
		$this->updateChilds();
		
		return $this;
	}
	
	public function getData() {
		$res = array();
		
		foreach($this->_fields as $field) {
			if($field instanceof \Asgard\Form\Field)
				$res[$field->name] = $field->getValue();
			elseif($field instanceof \Asgard\Form\Group)
				$res[$field->_groupName] = $field->getData();
		}
		
		return $res;
	}
	
	public function hasFile() {
		if($this->_hasfile === true)
			return true;
		foreach($this->_fields as $name=>$field) {
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
	
		foreach($this->_fields as $name=>$field) {
			if($field instanceof self) {
				$errors[$name] = $field->errors();
				if(count($errors[$name]) === 0)
					unset($errors[$name]);
			}
		}

		$this->_errors = $errors + $this->myErrors();

		$this->setErrors($this->_errors);

		return $this->_errors;
	}
	
#validation du formulaire.
	// -pouvoir specifier les messages d'erreurs
		// title => '..'
		// title.length => '..'
		// utiliser l'api de respect
	// no// -obtenir tous les messages d'erreurs par champs ou un message d'erreur pour un champs
	// -ca necessite que tous les sousformulaires/groupes soient valides en meme temps?
		// soit tout passe par Respect
		// ou Form se charge d'agreger les messages d'erreurs

	// ->Form agrege les erreurs/messages avec l'iterator de respect.
	// ->Pour definir les messages, utiliser ma propre api, eventuellement inspiree de respect.

	// ->obtenir tous les champs invalides de respect
	// ->generer les messages selon ceux fournis a form

	// adsf

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
		
		foreach($this->_fields as $name=>$field) {
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
		$data = $this->_data;

		$report = $this->getValidator()->errors($data);

		$errors = array();
		foreach($this->_fields as $name=>$field) {
			if($field instanceof Fields\FileField && isset($this->_data[$name])) {
				$f = $this->_data[$name];
				switch($f['error']) {
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
			foreach($group->_fields as $name=>$field) {
				if($field instanceof self)
					$field->_save($field);
			}
		}
	}
	
	public function isValid() {
		return $this->getValidator()->valid();
	}
	
	public function remove($name) {
		unset($this->_fields[$name]);
	}
	
	public function __unset($name) {
		$this->remove($name);
	}

	public function get($name) {
		return $this->_fields[$name];
	}

	public function add($name, $field, array $options=array()) {
		$fieldClass = $field.'Field';
		$this->__set($name, new $fieldClass($options));
	}
	
	public function __get($name) {
		return $this->get($name);
	}
	
	public function __set($name, $value) {
		$this->_fields[$name] = $this->parseFields($value, $name);
		
		return $this;
	}

	public function __isset($name) {
		return isset($this->_fields[$name]);
	}
	
	/* IMPLEMENTS */
	
    public function offsetSet($offset, $value) {
		if(is_null($offset))
			$this->_fields[] = $this->parseFields($value, count($this->_fields));
		else
			$this->_fields[$offset] = $this->parseFields($value, $offset);
    }
	
    public function offsetExists($offset) {
		return isset($this->_fields[$offset]);
    }
	
    public function offsetUnset($offset) {
		unset($this->_fields[$offset]);
    }
	
    public function offsetGet($offset) {
		return isset($this->_fields[$offset]) ? $this->_fields[$offset] : null;
    }
	
    public function rewind() {
		reset($this->_fields);
    }
  
    public function current() {
		return current($this->_fields);
    }
  
    public function key()  {
		return key($this->_fields);
    }
  
    public function next()  {
		return next($this->_fields);
    }
	
    public function valid() {
		$key = key($this->_fields);
		return $key !== NULL && $key !== FALSE;
    }

	public function trigger($name, array $args=array(), $cb=null, $print=false) {
		return parent::trigger($name, array_merge(array($this), $args), $cb, $print);
	}
	
	protected function updateChilds() {
		foreach($this->_fields as $name=>$field) {
			if($field instanceof \Asgard\Form\Group) {
				$field->setData(
					(isset($this->_data[$name]) ? $this->_data[$name]:array())
				);
			}
			elseif($field instanceof \Asgard\Form\Field) {
				if(isset($this->_data[$name]))
					$field->setValue($this->_data[$name]);
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
}