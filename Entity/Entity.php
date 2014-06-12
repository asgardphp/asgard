<?php
namespace Asgard\Entity;

abstract class Entity {
	protected static $app;
	#public for behaviors
	public $data = [
		'properties'	=>	[],
	];
	protected $locale;
	protected $locales;

	public function __construct(array $params=null) {
		#create the entity definition if does not exist yet
		static::$app['entitiesmanager']->make(get_called_class());
		$this->locale = static::$app['config']['locale'];
		$this->locales = static::$app['config']['locales'];

		$this->loadDefault();
		if(is_array($params))
			$this->set($params);
	}

	public function setLocale($locale) {
		$this->locale = $locale;
	}

	public function getLocale() {
		return $this->locale;
	}

	public static function setApp($app) {
		static::$app = $app;
	}

	public static function getapp() {
		return static::$app;
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
		return static::$app['entitiesmanager']->get(get_called_class());
	}

	public function loadDefault() {
		foreach(static::properties() as $name=>$property)
			$this->set($name, $property->getDefault($this, $name));
				
		return $this;
	}
	
	/* VALIDATION */
	public function getValidator() {
		$constrains = [];
		$messages = [];
		$validator = new \Asgard\Validation\Validator;

		foreach($this->getDefinition()->properties() as $name=>$property) {
			if($property->get('multiple')) {
				$constrains[$name] = [];
				foreach($this->get($name) as $k=>$v) {
					$validator->attribute($name.'.'.$k, $property->getRules());
					$validator->attribute($name.'.'.$k)->formatParameters(function(&$params) use($name) {
						$params['attribute'] = $name;
					});
				}
			}
			else
				$validator->attribute($name, $property->getRules());
			$messages[$name] = $property->getMessages();
		}

		$messages = array_merge($messages, static::getDefinition()->messages());
		
		$validator->set('entity', $this);
		$validator->setRegistry(static::$app['rulesregistry']);
		$validator->ruleMessages($messages);

		return $validator;
	}
	
	public function valid() {
		$data = $this->toArrayRaw();
		$validator = $this->getValidator();
		return $this->getDefinition()->trigger('validation', [$this, $validator, &$data], function($chain, $entity, $validator, &$data) {
			return $validator->valid($data);
		});
	}
	
	public function errors() {
		$data = $this->toArrayRaw();
		$validator = $this->getValidator();
		$errors = $this->getDefinition()->trigger('validation', [$this, $validator, &$data], function($chain, $entity, $validator, &$data) {
			return $validator->errors($data);
		});

		$e = [];
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
					$lang = $this->locale;
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
		if(is_array($name)) {
			$lang = $value;
			$vars = $name;
			foreach($vars as $name=>$value)
				$this->set($name, $value, $lang, false);
			return $this;
		}

		if(static::getDefinition()->hasProperty($name)) {
			if(!$lang)
				$lang = $this->getLocale();

			static::getDefinition()->processBeforeSet($this, $name, $value, $lang, $hook);

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
			$lang = $this->locale;
		$entity = $this;

		if($res = $this->getDefinition()->trigger('get', [$this, $name, $lang]))
			return $res;

		if($this->getDefinition()->hasProperty($name)) {
			if($entity::property($name)->i18n) {
				if($lang == 'all') {
					$langs = $this->locales;
					$res = [];
					foreach($langs as $lang)
						$res[$lang] = $entity->get($name, $lang);
					return $res;
				}
				elseif(isset($entity->data['properties'][$name][$lang]))
					return $entity->data['properties'][$name][$lang];
				else {
					$i18n = $this->getDefinition()->trigger('getI18N', [$this, $name, $lang]);
					if($i18n === null) $i18n = [];
					foreach ($i18n as $k=>$v)
						$this->_set($k, $v, $lang);
					if(!isset($entity->data['properties'][$name][$lang]))
						return null;
					return $entity->data['properties'][$name][$lang];
				}
			}
			elseif(isset($entity->data['properties'][$name]))
				return $entity->data['properties'][$name];
		}
		elseif(isset($entity->data[$name]))
			return $entity->data[$name];
	}
	
	/* UTILS */
	public function toArrayRaw() {
		$res = [];
		
		foreach($this->properties() as $name=>$property) {
			if(isset($this->data['properties'][$name])) {
				if($this->property($name)->get('multiple'))
					$res[$name] = $this->get($name)->all();
				else
					$res[$name] = $this->get($name);
			}
			else
				$res[$name] = null;
		}
		
		return $res;
	}
	
	public function toArray() {
		$res = [];
		
		foreach($this->properties() as $name=>$property) {
			$res[$name] = $this->get($name);
			if($property->get('multiple')) {
				foreach($res[$name] as $k=>$v)
					$res[$name][$k] = $this->propertyToArray($v, $property);
			}
			else
				$res[$name] = $this->propertyToArray($res[$name], $property);
		}
		
		return $res;
	}

	private function propertyToArray($v, $property) {
		if(is_string($v) || is_array($v))
			return $v;
		if(method_exists($property, 'toArray'))
			return $property->toArray($v);
		elseif(method_exists($property, 'toString'))
			return $property->toString($v);
		elseif(is_object($v)) {
			if(method_exists($v, 'toArray'))
				return $v->toArray();
			elseif(method_exists($v, '__toString'))
				return $v->__toString();
		}
		throw new \Exception('Cannot convert property '.$property.' to array or string.');
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
