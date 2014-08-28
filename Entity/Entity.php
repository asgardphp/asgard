<?php
namespace Asgard\Entity;

abstract class Entity {
	#public for behaviors
	public $data = [
		'properties'   => [],
		'translations' => [],
	];
	protected $definition;
	protected $locale;

	public function __construct(array $params=null, $locale=null) {
		$this->setLocale($locale);
		$this->loadDefault();
		if(is_array($params))
			$this->set($params);
	}

	public function getLocale() {
		if($this->locale === null)
			$this->locale = $this->getEntitiesManager()->getDefaultLocale();
		return $this->locale;
	}

	public function setDefinition($definition) {
		$this->definition = $definition;
		return $this;
	}

	public function setLocale($locale) {
		$this->locale = $locale;
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
		return static::getStaticDefinition()->callStatic($name, $arguments);
	}

	public function __call($name, array $arguments) {
		return $this->getDefinition()->call($this, $name, $arguments);
	}
	
	/* INIT AND ENTITY CONFIGURATION */
	public static function definition(EntityDefinition $entityDefinition) {}

	public function getDefinition() {
		if(isset($this->definition))
			return $this->definition;
		else
			return $this->definition = static::getStaticDefinition();
	}

	#only for entities without dependency injection, activerecord like, e.g. new Article or Article::find();
	public static function getStaticDefinition() {
		return EntitiesManager::singleton()->get(get_called_class());
	}

	public function loadDefault() {
		foreach($this->getDefinition()->properties() as $name=>$property)
			$this->set($name, $property->getDefault($this, $name));
				
		return $this;
	}
	
	/* VALIDATION */
	public function getValidator(array $locales=[]) {
		$messages = [];
		$validator = $this->getDefinition()->getEntitiesManager()->createValidator();

		foreach($this->getDefinition()->properties() as $name=>$property) {
			if($locales && $property->i18n) {
				foreach($locales as $locale) {
					if($property->get('multiple')) {
						foreach($this->get($name) as $k=>$v) {
							$validator->attribute($name.'.'.$locale.'.'.$k, $property->getRules());
							$validator->attribute($name.'.'.$locale.'.'.$k)->formatParameters(function(&$params) use($name) {
								$params['attribute'] = $name;
							});
						}
					}
					else {
						$validator->attribute($name.'.'.$locale, $property->getRules());
						$validator->attribute($name.'.'.$locale)->formatParameters(function(&$params) use($name) {
							$params['attribute'] = $name;
						});
					}
				}
			}
			else {
				if($property->get('multiple')) {
					foreach($this->get($name) as $k=>$v) {
						$validator->attribute($name.'.'.$k, $property->getRules());
						$validator->attribute($name.'.'.$k)->formatParameters(function(&$params) use($name) {
							$params['attribute'] = $name;
						});
					}
				}
				else
					$validator->attribute($name, $property->getRules());
			}

			$messages[$name] = $property->getMessages();
		}

		$messages = array_merge($messages, $this->getDefinition()->messages());
		
		$validator->set('entity', $this);
		$validator->attributesMessages($messages);

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
	public function _set($name, $value=null, $locale=null) {
		if(is_array($name)) {
			$locale = $value;
			$vars = $name;
			foreach($vars as $name=>$value)
				$this->_set($name, $value, $locale);
			return $this;
		}

		if($this->getDefinition()->hasProperty($name)) {
			if(!$locale)
				$locale = $this->getLocale();
			if($this->getDefinition()->property($name)->i18n && $locale !== $this->getLocale()) {
				if($locale == 'all') {
					foreach($value as $locale => $v)
						$this->data['translations'][$locale][$name] = $v;
				}
				else
					$this->data['translations'][$locale][$name] = $value;
			}
			else
				$this->data['properties'][$name] = $value;
		}
		elseif($name !== 'translations' && $name !== 'properties')
			$this->data[$name] = $value;
		
		return $this;
	}

	public function set($name, $value=null, $locale=null, $hook=true) {
		#setting multiple properties at once
		if(is_array($name)) {
			$vars = $name;
			$locale = $value;
			foreach($vars as $name=>$value)
				$this->set($name, $value, $locale, $hook);
			return $this;
		}

		#setting a property multiple translations at once
		if(is_array($locale)) {
			foreach($locale as $one)
				$this->set($name, $value[$one], $one, $hook);
			return $this;
		}

		$this->getDefinition()->processBeforeSet($this, $name, $value, $locale, $hook);

		if($this->getDefinition()->hasProperty($name)) {
			if(!$locale)
				$locale = $this->getLocale();

			if($this->getDefinition()->property($name)->i18n && $locale !== $this->getLocale())
				$this->data['translations'][$locale][$name] = $value;
			else
				$this->data['properties'][$name] = $value;
		}
		else
			$this->data[$name] = $value;
				
		return $this;
	}

	public function _get($name, $locale=null) {
		if(!$locale)
			$locale = $this->getLocale();
		
		if($this->getDefinition()->hasProperty($name)) {
			if($this->getDefinition()->property($name)->i18n && $locale !== $this->getLocale()) {
				#multiple locales at once
				if(is_array($locale)) {
					$res = [];
					foreach($locale as $_locale)
						$res[$_locale] = $this->get($name, $_locale);
					return $res;
				}
				elseif(isset($this->data['translations'][$locale][$name]))
					return $this->data['translations'][$locale][$name];
				else {
					$i18n = $this->getDefinition()->trigger('getI18N', [$this, $name, $locale]);
					if($i18n === null)
						$i18n = [];
					foreach($i18n as $k=>$v)
						$this->_set($k, $v, $locale);
					if(!isset($this->data['translations'][$locale][$name]))
						return null;
					return $this->data['translations'][$locale][$name];
				}
			}
			elseif(isset($this->data['properties'][$name]))
				return $this->data['properties'][$name];
		}
		elseif(isset($this->data[$name]))
			return $this->data[$name];
	}
	
	public function get($name, $locale=null) {
		if(!$locale)
			$locale = $this->getLocale();

		if(($res = $this->getDefinition()->trigger('get', [$this, $name, $locale])) !== null)
			return $res;

		return $this->_get($name, $locale);
	}
	
	/* UTILS */
	public function toArrayRaw() {
		$res = [];
		
		foreach($this->getDefinition()->properties() as $name=>$property) {
			if($this->getDefinition()->property($name)->get('multiple'))
				$res[$name] = $this->get($name)->all();
			else
				$res[$name] = $this->get($name);
		}
		
		return $res;
	}
	
	public function toArray() {
		$res = [];
		
		foreach($this->getDefinition()->properties() as $name=>$property) {
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

	public function toJSON() {
		return json_encode($this->toArray());
	}

	public static function arrayToJSON(array $entities) {
		foreach($entities as $k=>$entity)
			$entities[$k] = $entity->toArray();
		return json_encode($entities);
	}

	private function propertyToArray($v, $property) {
		if(is_null($v))
			return null;
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
		throw new \Exception('Cannot convert property '.get_class($property).' to array or string.');
	}

	/* I18N */
	public function translate($locale) {
		$localeEntity = clone $this;
		$localeEntity->setLocale($locale);
		if(isset($this->data['translations'][$locale]))
			$localeEntity->_set($this->data['translations'][$locale]);
		unset($localeEntity->data['translations'][$locale]);
		$localeEntity->data['translations'][$this->getLocale()] = $this->data['properties'];

		return $localeEntity;
	}

	public function getLocales() {
		return array_merge([$this->getLocale()], array_keys($this->data['translations']));
	}

	public function toArrayRawI18N(array $locales=[]) {
		if(!$locales)
			$locales = $this->getLocales();
		$res = [];
		
		foreach($this->getDefinition()->properties() as $name=>$property) {
			if($property->i18n) {
				foreach($locales as $locale) {
					if($this->getDefinition()->property($name)->get('multiple'))
						$res[$name][$locale] = $this->get($name, $locale)->all();
					else
						$res[$name][$locale] = $this->get($name, $locale);
				}
			}
			elseif($this->getDefinition()->property($name)->get('multiple'))
				$res[$name] = $this->get($name)->all();
			else
				$res[$name] = $this->get($name);
		}

		return $res;
	}

	public function toArrayI18N(array $locales=[]) {
		if(!$locales)
			$locales = $this->getLocales();
		$res = [];
		
		foreach($this->getDefinition()->properties() as $name=>$property) {
			if($property->i18n) {
				foreach($locales as $locale) {
					if($this->getDefinition()->property($name)->get('multiple')) {
						foreach($this->get($name, $locale)->all() as $k=>$v)
							$res[$name][$locale][$k] = $this->propertyToArray($v, $property);
					}
					else
						$res[$name][$locale] = $this->propertyToArray($this->get($name, $locale), $property);
				}
			}
			elseif($this->getDefinition()->property($name)->get('multiple')) {
				$res[$name] = [];
				foreach($this->get($name)->all() as $k=>$v)
					$res[$name][$k] = $this->propertyToArray($v, $property);
			}
			else
				$res[$name] = $this->propertyToArray($this->get($name), $property);
		}
		
		return $res;
	}

	public function toJSONI18N(array $locales=[]) {
		if(!$locales)
			$locales = $this->getLocales();
		return json_encode($this->toArrayI18N($locales));
	}

	public static function arrayToJSONI18N(array $entities, array $locales=[]) {
		foreach($entities as $k=>$entity)
			$entities[$k] = $entity->toArrayI18N($locales);
		return json_encode($entities);
	}
	
	public function validI18N(array $locales=[]) {
		if(!$locales)
			$locales = $this->getLocales();
		$data = $this->toArrayRawI18N($locales);
		$validator = $this->getValidator($locales);
		return $this->getDefinition()->trigger('validation', [$this, $validator, &$data], function($chain, $entity, $validator, &$data) {
			return $validator->valid($data);
		});
	}
	
	public function errorsI18N(array $locales=[]) {
		if(!$locales)
			$locales = $this->getLocales();
		$data = $this->toArrayRawI18N($locales);
		$validator = $this->getValidator($locales);
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
}
