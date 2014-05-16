<?php
namespace Asgard\Entity;

abstract class Entity {
	#public for behaviors
	public $data = array(
		'properties'	=>	array(),
	);

	public function __construct(array $params=null) {
		#create the entity definition if does not exist yet
		\Asgard\Core\App::get('entitiesmanager')->make(get_called_class());

		$this->loadDefault();
		if(is_array($params))
			$this->set($params);
	}
	
	/* MAGIC METHODS */
	public function __set($name, $value) {
		$this->set($name, $value);
	}

	public function __get($name) {
		return $this->get($name);
	}
	
	public function __isset($name) {
		return isset($this->data['properties'][$name]);
	}
	
	public function __unset($name) {
		unset($this->data['properties'][$name]);
	}

	public static function __callStatic($name, array $arguments) {
		return static::getDefinition()->callStatic($name, $arguments);
	}

	public function __call($name, array $arguments) {
		return static::getDefinition()->call($this, $name, $arguments);
	}
	
	/* INIT AND ENTITY CONFIGURATION */
	public static function definition(EntityDefinition $entityDefinition) {}

	public static function getDefinition() {
		return \Asgard\Core\App::get('entitiesmanager')->get(get_called_class());
	}

	public function loadDefault() {
		foreach(static::properties() as $name=>$property)
			$this->set($name, $property->getDefault($this));
				
		return $this;
	}
	
	/* VALIDATION */
	public function getValidator() {
		$constrains = array();
		$messages = array();
		$entity = $this;

		foreach($this->getDefinition()->properties() as $name=>$property)
			$constrains[$name] = $property->getRules();
		foreach($this->getDefinition()->properties() as $name=>$property)
			$messages[$name] = $property->getMessages();

		$messages = array_merge($messages, static::getDefinition()->messages());
		
		$validator = new \Asgard\Validation\Validator;
		$validator->attributes($constrains);
		$validator->ruleMessages($messages);

		return $validator;
	}
	
	public function valid() {
		$data = $this->toArrayRaw();
		$validator = $this->getValidator();
		return $this->getDefinition()->trigger('validation', array($this, $validator, &$data), function($chain, $entity, $validator, &$data) {
			return $validator->valid($data);
		});
	}
	
	public function errors() {
		$data = $this->toArrayRaw();
		$validator = $this->getValidator();
		$errors = $this->getDefinition()->trigger('validation', array($this, $validator, &$data), function($chain, $entity, $validator, &$data) {
			return $validator->errors($data);
		});

		$e = array();
		foreach($data as $property=>$value) {
			if($propertyErrors = $errors->attribute($property)->errors())
				$e[$property] = $propertyErrors;
		}

		return $e;
	}

	/* ACCESSORS */
	public function _set($name, $value=null, $lang=null) {
		if(is_array($name)) {
			$lang = $value;
			$vars = $name;
			foreach($vars as $name=>$value)
				$this->_set($name, $value, $lang);
			return $this;
		}

		if(static::getDefinition()->hasProperty($name)) {
			if(static::getDefinition()->property($name)->i18n) {
				if(!$lang)
					$lang = \Asgard\Core\App::get('config')->get('locale');
				if($lang == 'all') {
					foreach($value as $one => $v)
						$this->data['properties'][$name][$one] = $v;
				}
				else
					$this->data['properties'][$name][$lang] = $value;
			}
			else
				$this->data['properties'][$name] = $value;
		}
		else
			$this->data[$name] = $value;
				
		return $this;
	}

	public function set($name, $value=null, $lang=null, $hook=true) {
		if($hook)
			$this->trigger('set', array($this, $name, &$value, $lang));

		if(is_array($name)) {
			$lang = $value;
			$vars = $name;
			foreach($vars as $name=>$value)
				$this->set($name, $value, $lang, false);
			return $this;
		}

		if(static::getDefinition()->hasProperty($name)) {
			if(static::getDefinition()->property($name)->setHook) {
				$hook = static::getDefinition()->property($name)->setHook;
				$value = call_user_func_array($hook, array($value));
			}

			if(static::getDefinition()->property($name)->i18n) {
				if(!$lang)
					$lang = \Asgard\Core\App::get('config')->get('locale');
				if($lang == 'all') {
					$val = array();
					foreach($value as $one => $v)
						$val[$one] = static::getDefinition()->property($name)->set($v, $this);
					$value = $val;
				}
				else
					$value = static::getDefinition()->property($name)->set($value, $this);
			}
			else
				$value = static::getDefinition()->property($name)->set($value, $this);

			if(static::getDefinition()->property($name)->i18n && $lang != 'all')
				$this->data['properties'][$name][$lang] = $value;
			else
				$this->data['properties'][$name] = $value;
		}
		else
			$this->data[$name] = $value;
				
		return $this;
	}
	
	public function get($name, $lang=null) {
		if(!$lang)
			$lang = \Asgard\Core\App::get('config')->get('locale');

		$res = $this->getDefinition()->trigger('get', array($this, $name, $lang), function($chain, $entity, $name, $lang) {
			if($entity::hasProperty($name)) {
				if($entity::property($name)->i18n) {
					if($lang == 'all') {
						$langs = \Asgard\Core\App::get('config')->get('locales');
						$res = array();
						foreach($langs as $lang)
							$res[$lang] = $entity->get($name, $lang);
						return $res;
					}
					elseif(isset($entity->data['properties'][$name][$lang]))
						return $entity->data['properties'][$name][$lang];
				}
				elseif(isset($entity->data['properties'][$name])) 
					return $entity->data['properties'][$name];
			}
			elseif(isset($entity->data[$name]))
				return $entity->data[$name];
		});

		return $res;
	}
	
	/* UTILS */
	public function toArrayRaw() {
		$res = array();
		
		foreach($this->propertyNames() as $name) {
			if(isset($this->data['properties'][$name]))
				$res[$name] = $this->data['properties'][$name];
			else
				$res[$name] = null;
		}
		
		return $res;
	}
	
	public function toArray() {
		$res = array();
		
		foreach($this->properties() as $name=>$property) {
			$res[$name] = $this->$property;
			if(method_exists($property, 'toArray'))
				$res[$name] = $property->toArray($res[$name]);
			elseif(method_exists($property, 'toString'))
				$res[$name] = $property->toString($res[$name]);
			else {
				if(is_object($res[$name])) {
					if(method_exists($res[$name], 'toArray'))
						$res[$name] = $res[$name]->toArray();
					elseif(method_exists($res[$name], '__toString'))
						$res[$name] = $res[$name]->__toString();
				}	
			}
		}
		
		return $res;
	}

	public function toJSON() {
		return json_encode($this->toArray());
	}

	public static function arrayToJSON(array $entities) {
		foreach($entities as $k=>$entity)
			$entities[$k] = $entity->toArray();
		return json_encode($entities);
	}
}
