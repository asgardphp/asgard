<?php
namespace Asgard\Core;

class EntityException extends \Exception {
	public $errors = array();
}

abstract class Entity {
	#public for behaviors
	public $data = array(
		'properties'	=>	array(),
	);

	public function __construct($param=null) {
		$chain = new \Asgard\Hook\HookChain();
		$chain->found = false;
		$this->triggerChain($chain, 'construct', array($this, $param));
		if(!$chain->found) {
			if(is_array($param))
				$this->loadDefault()->set($param);
			else
				$this->loadDefault();
		}
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

	public static function __callStatic($name, $arguments) {
		$chain = new \Asgard\Hook\HookChain();
		$chain->found = false;
		$res = static::triggerChain($chain, 'callStatic', array($name, $arguments));
		if(!$chain->found)
			throw new \Exception('Static method '.$name.' does not exist for entity '.get_called_class());

		return $res;
	}

	public function __call($name, $arguments) {
		$chain = new \Asgard\Hook\HookChain;
		$chain->found = false;
		$res = static::triggerChain($chain, 'call', array($this, $name, $arguments));
		if(!$chain->found) {
			try {
				return static::__callStatic($name, $arguments);
			} catch(\ErrorException $e) {
				throw new \Exception('Method '.$name.' does not exist for entity '.get_called_class());
			}
		}

		return $res;
	}
	
	/* INIT AND Entity CONFIGURATION */
	public static function configure($entityDefinition) {}

	public static function getDefinition() {
		return \Asgard\Core\App::get('entitiesmanager')->get(get_called_class());
	}

	public function loadDefault() {
		foreach(static::properties() as $name=>$property)
			$this->set($name, $property->getDefault($this));
				
		return $this;
	}

	/* PERSISTENCY */
	public function save($values=null, $force=false) {
		#set $values if any
		if($values)
			$this->set($values);
		
		$this->trigger('behaviors_presave', array($this));
		
		if(!$force) {
			#validate params and files
			if($errors = $this->errors()) {
				$msg = implode("\n", \Asgard\Utils\Tools::flateArray($errors));
				$e = new EntityException($msg);
				$e->errors = $errors;
				throw $e;
			}
		}
		
		$chain = new \Asgard\Hook\HookChain;
		$this->triggerChain($chain, 'save', array($this));
		if(!$chain->executed)
			throw new \Exception('Cannot save non-persistent Entities');

		return $this;
	}
	
	public function destroy() {
		$chain = new \Asgard\Hook\HookChain;
		$this->triggerChain($chain, 'destroy', array($this));
		if(!$chain->executed)
			throw new \Exception('Cannot destroy non-persistent Entities');
	}

	public static function create($values=array(), $force=false) {
		$m = new static;
		return $m->save($values, $force);
	}
	
	/* VALIDATION */
	protected function getValidator() {
		$constrains = array();
		$messages = array();
		$entity = $this;
		$this->trigger('constrains', array(&$constrains), function($chain, &$constrains) use($entity) {
			foreach($entity->getDefinition()->properties as $name=>$property)
				$constrains[$name] = $property->getRules();
		});
		$this->trigger('messages', array(&$constrains), function($chain, &$constrains) use($entity) {
			foreach($entity->getDefinition()->properties as $name=>$property)
				$messages[$name] = $property->getMessages();
		});

		$messages = array_merge($messages, static::getDefinition()->messages());
		
		$validator = new \Asgard\Validation\Validator;
		$validator->attributes($constrains);
		$validator->ruleMessages($messages);

		return $validator;
	}
	
	public function valid() {
		$data = $this->toArrayRaw();
		return $this->getValidator()->valid($data);
	}
	
	public function errors() {
		$data = $this->toArrayRaw();

		$errors = array();
		$this->trigger('validation', array($this, &$data, &$errors), function($chain, $entity, &$data, &$errors) {
			$errors = $entity->getValidator()->errors($data);
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
			$this->trigger('set', array($this, $name, $value, $lang));

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

		$res = static::trigger('get', array($this, $name, $lang), function($chain, $entity, $name, $lang) {
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
	
	public function toArrayRaw() {
		$vars = array();
		
		foreach($this->propertyNames() as $attr) {
			if(isset($this->data['properties'][$attr]))
				$vars[$attr] = $this->data['properties'][$attr];
			else
				$vars[$attr] = null;
		}
		foreach($this->getDefinition()->relations() as $name=>$relation)
			$vars[$name] = $this->relation($name);
		
		return $vars;
	}
	
	public function toArray() {
		$vars = array();
		
		foreach($this->properties() as $name=>$property) {
			$vars[$name] = $this->$property;
			if(method_exists($property, 'toArray'))
				$vars[$name] = $property->toArray($vars[$name]);
			elseif(method_exists($property, 'toString'))
				$vars[$name] = $property->toString($vars[$name]);
			else {
				if(is_object($vars[$name])) {
					if(method_exists($vars[$name], 'toArray'))
						$vars[$name] = $vars[$name]->toArray();
					elseif(method_exists($vars[$name], '__toString'))
						$vars[$name] = $vars[$name]->__toString();
				}	
			}
		}
		
		foreach($this->getDefinition()->relations() as $name=>$relation) {
			if(isset($this->data[$name])) {
				if(is_array($this->data[$name])) {
					$res = array();
					foreach($this->data[$name] as $relation_entity)
						$res[] = $relation_entity->toArray();
				}
				else
					$res = $this->data[$name]->toArray();

				$vars[$name] = $res;
			}
		}
		
		return $vars;
	}

	public static function arrayToJSON($arr) {
		$res = array();
		foreach($arr as $relation_entity)
			$res[] = $relation_entity->toArray();
		return json_encode($res);
	}

	public function toJSON() {
		return json_encode($this->toArray());
	}
	
	/* Definition */
	public static function getEntityName() {
		return \Asgard\Utils\NamespaceUtils::basename(strtolower(get_called_class()));
	}

	public static function hasProperty($name) {
		return static::getDefinition()->hasProperty($name);
	}

	public static function addProperty($property, $params) {
		return static::getDefinition()->addProperty($property, $params);
	}
	
	public static function property($name) {
		return static::getDefinition()->property($name);
	}
	
	public static function properties() {
		return static::getDefinition()->properties();
	}

	public static function propertyNames() {
		return array_keys(static::getDefinition()->properties());
	}
	
	public static function isI18N() {
		return static::getDefinition()->isI18N();
	}

	/* HOOKS */
	protected static function trigger($name, $args=array(), $cb=null) {
		return static::getDefinition()->trigger($name, $args, $cb);
	}

	protected static function triggerChain($chain, $name, $args=array(), $cb=null) {
		return static::getDefinition()->triggerChain($chain, $name, $args, $cb);
	}

	public static function hook() {
		return static::getDefinition()->hook();
	}

	public static function hookOn($hookName, $cb) {
		return static::getDefinition()->hookOn($hookName, $cb);
	}

	public static function hookBefore($hookName, $cb) {
		return static::getDefinition()->hookBefore($hookName, $cb);
	}

	public static function hookAfter($hookName, $cb) {
		return static::getDefinition()->hookAfter($hookName, $cb);
	}

	/* utils functions */
	public static function EntitiesToArray($entities) {
		foreach($entities as $k=>$v)
			$entities[$k] = json_decode($v->toJSON());
		return json_encode($entities);
	}
}
