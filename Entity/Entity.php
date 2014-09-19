<?php
namespace Asgard\Entity;

/**
 * 
 */
abstract class Entity {
	/**
	 * [$data description]
	 * @var [type]
	 */
	public $data = [ #public for behaviors
		'properties'   => [],
		'translations' => [],
	];
	/**
	 * [$definition description]
	 * @var [type]
	 */
	protected $definition;
	/**
	 * [$locale description]
	 * @var [type]
	 */
	protected $locale;

	/**
	 * [__construct description]
	 * @param [type] $params
	 * @param [type] $locale
	 */
	public function __construct(array $params=null, $locale=null) {
		$this->setLocale($locale);
		$this->loadDefault();
		if(is_array($params))
			$this->set($params);
	}

	/**
	 * [getLocale description]
	 * @return [type]
	 */
	public function getLocale() {
		if($this->locale === null)
			$this->locale = $this->getDefinition()->getEntitiesManager()->getDefaultLocale();
		return $this->locale;
	}

	/**
	 * [setDefinition description]
	 * @param [type] $definition
	 */
	public function setDefinition($definition) {
		$this->definition = $definition;
		return $this;
	}

	/**
	 * [setLocale description]
	 * @param [type] $locale
	 */
	public function setLocale($locale) {
		$this->locale = $locale;
	}
	
	/**
	 * [__set description]
	 * @param [type] $name
	 * @param [type] $value
	 */
	public function __set($name, $value) {
		$this->set($name, $value);
	}

	/**
	 * [__get description]
	 * @param  [type] $name
	 * @return [type]
	 */
	public function __get($name) {
		return $this->get($name);
	}
	
	/**
	 * [__isset description]
	 * @param  [type]  $name
	 * @return boolean
	 */
	public function __isset($name) {
		return isset($this->data['properties'][$name]);
	}
	
	/**
	 * [__unset description]
	 * @param [type] $name
	 */
	public function __unset($name) {
		unset($this->data['properties'][$name]);
	}

	/**
	 * __callStatic magic method. For active-record like entities only.
	 * @param  [type] $name
	 * @param  array  $arguments
	 * @return [type]
	 */
	public static function __callStatic($name, array $arguments) {
		return static::getStaticDefinition()->callStatic($name, $arguments);
	}

	/**
	 * [__call description]
	 * @param  [type] $name
	 * @param  array  $arguments
	 * @return [type]
	 */
	public function __call($name, array $arguments) {
		return $this->getDefinition()->call($this, $name, $arguments);
	}
	
	/**
	 * [definition description]
	 * @param  EntityDefinition $entityDefinition
	 * @return [type]
	 */
	public static function definition(EntityDefinition $entityDefinition) {}

	/**
	 * [getDefinition description]
	 * @return [type]
	 */
	public function getDefinition() {
		if(isset($this->definition))
			return $this->definition;
		else
			return $this->definition = static::getStaticDefinition();
	}

	/**
	 * [getStaticDefinition description]
	 * @return [type]
	 */
	public static function getStaticDefinition() {
		#only for entities without dependency injection, activerecord like, e.g. new Article or Article::find();
		return EntitiesManager::singleton()->get(get_called_class());
	}

	/**
	 * [loadDefault description]
	 * @return [type]
	 */
	public function loadDefault() {
		foreach($this->getDefinition()->properties() as $name=>$property)
			$this->set($name, $property->getDefault($this, $name));
				
		return $this;
	}
	
	/**
	 * [getValidator description]
	 * @param  [type] $locales
	 * @return [type]
	 */
	public function getValidator(array $locales=[]) {
		$messages = [];
		$validator = $this->getDefinition()->getEntitiesManager()->createValidator();

		foreach($this->getDefinition()->properties() as $name=>$property) {
			if($locales && $property->i18n) {
				foreach($locales as $locale) {
					if($property->get('many')) {
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
				if($property->get('many')) {
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
	
	/**
	 * [valid description]
	 * @return [type]
	 */
	public function valid() {
		$data = $this->toArrayRaw();
		$validator = $this->getValidator();
		return $this->getDefinition()->trigger('validation', [$this, $validator, &$data], function($chain, $entity, $validator, &$data) {
			return $validator->valid($data);
		});
	}
	
	/**
	 * [errors description]
	 * @return [type]
	 */
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

	/**
	 * [_set description]
	 * @param [type] $name
	 * @param [type] $value
	 * @param [type] $locale
	 */
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

	/**
	 * [set description]
	 * @param [type]  $name
	 * @param [type]  $value
	 * @param [type]  $locale
	 * @param boolean $hook
	 */
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

	/**
	 * [_get description]
	 * @param  [type] $name
	 * @param  [type] $locale
	 * @return [type]
	 */
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
	
	/**
	 * [get description]
	 * @param  [type] $name
	 * @param  [type] $locale
	 * @return [type]
	 */
	public function get($name, $locale=null) {
		if(!$locale)
			$locale = $this->getLocale();

		if(($res = $this->getDefinition()->trigger('get', [$this, $name, $locale])) !== null)
			return $res;

		return $this->_get($name, $locale);
	}
	
	/**
	 * [toArrayRaw description]
	 * @return [type]
	 */
	public function toArrayRaw() {
		$res = [];
		
		foreach($this->getDefinition()->properties() as $name=>$property) {
			if($this->getDefinition()->property($name)->get('type') == 'entity')
				continue;
			if($this->getDefinition()->property($name)->get('many'))
				$res[$name] = $this->get($name)->all();
			else
				$res[$name] = $this->get($name);
		}
		
		return $res;
	}
	
	/**
	 * [toArray description]
	 * @return [type]
	 */
	public function toArray() {
		$res = [];
		
		foreach($this->getDefinition()->properties() as $name=>$property) {
			if($this->getDefinition()->property($name)->get('type') == 'entity')
				continue;
			$res[$name] = $this->get($name);
			if($property->get('many')) {
				foreach($res[$name] as $k=>$v)
					$res[$name][$k] = $this->propertyToArray($v, $property);
			}
			else
				$res[$name] = $this->propertyToArray($res[$name], $property);
		}
		
		return $res;
	}

	/**
	 * [toJSON description]
	 * @return [type]
	 */
	public function toJSON() {
		return json_encode($this->toArray());
	}

	/**
	 * [arrayToJSON description]
	 * @param  array  $entities
	 * @return [type]
	 */
	public static function arrayToJSON(array $entities) {
		foreach($entities as $k=>$entity)
			$entities[$k] = $entity->toArray();
		return json_encode($entities);
	}

	/**
	 * [propertyToArray description]
	 * @param  [type] $v
	 * @param  [type] $property
	 * @return [type]
	 */
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

	/**
	 * [translate description]
	 * @param  [type] $locale
	 * @return [type]
	 */
	public function translate($locale) {
		$localeEntity = clone $this;
		$localeEntity->setLocale($locale);
		if(isset($this->data['translations'][$locale]))
			$localeEntity->_set($this->data['translations'][$locale]);
		unset($localeEntity->data['translations'][$locale]);
		$localeEntity->data['translations'][$this->getLocale()] = $this->data['properties'];

		return $localeEntity;
	}

	/**
	 * [getLocales description]
	 * @return [type]
	 */
	public function getLocales() {
		return array_merge([$this->getLocale()], array_keys($this->data['translations']));
	}

	/**
	 * [toArrayRawI18N description]
	 * @param  [type] $locales
	 * @return [type]
	 */
	public function toArrayRawI18N(array $locales=[]) {
		if(!$locales)
			$locales = $this->getLocales();
		$res = [];
		
		foreach($this->getDefinition()->properties() as $name=>$property) {
			if($property->i18n) {
				foreach($locales as $locale) {
					if($this->getDefinition()->property($name)->get('many'))
						$res[$name][$locale] = $this->get($name, $locale)->all();
					else
						$res[$name][$locale] = $this->get($name, $locale);
				}
			}
			elseif($this->getDefinition()->property($name)->get('many'))
				$res[$name] = $this->get($name)->all();
			else
				$res[$name] = $this->get($name);
		}

		return $res;
	}

	/**
	 * [toArrayI18N description]
	 * @param  [type] $locales
	 * @return [type]
	 */
	public function toArrayI18N(array $locales=[]) {
		if(!$locales)
			$locales = $this->getLocales();
		$res = [];
		
		foreach($this->getDefinition()->properties() as $name=>$property) {
			if($property->i18n) {
				foreach($locales as $locale) {
					if($this->getDefinition()->property($name)->get('many')) {
						foreach($this->get($name, $locale)->all() as $k=>$v)
							$res[$name][$locale][$k] = $this->propertyToArray($v, $property);
					}
					else
						$res[$name][$locale] = $this->propertyToArray($this->get($name, $locale), $property);
				}
			}
			elseif($this->getDefinition()->property($name)->get('many')) {
				$res[$name] = [];
				foreach($this->get($name)->all() as $k=>$v)
					$res[$name][$k] = $this->propertyToArray($v, $property);
			}
			else
				$res[$name] = $this->propertyToArray($this->get($name), $property);
		}
		
		return $res;
	}

	/**
	 * [toJSONI18N description]
	 * @param  [type] $locales
	 * @return [type]
	 */
	public function toJSONI18N(array $locales=[]) {
		if(!$locales)
			$locales = $this->getLocales();
		return json_encode($this->toArrayI18N($locales));
	}

	/**
	 * [arrayToJSONI18N description]
	 * @param  array  $entities
	 * @param  [type] $locales
	 * @return [type]
	 */
	public static function arrayToJSONI18N(array $entities, array $locales=[]) {
		foreach($entities as $k=>$entity)
			$entities[$k] = $entity->toArrayI18N($locales);
		return json_encode($entities);
	}
	
	/**
	 * [validI18N description]
	 * @param  [type] $locales
	 * @return [type]
	 */
	public function validI18N(array $locales=[]) {
		if(!$locales)
			$locales = $this->getLocales();
		$data = $this->toArrayRawI18N($locales);
		$validator = $this->getValidator($locales);
		return $this->getDefinition()->trigger('validation', [$this, $validator, &$data], function($chain, $entity, $validator, &$data) {
			return $validator->valid($data);
		});
	}
	
	/**
	 * [errorsI18N description]
	 * @param  [type] $locales
	 * @return [type]
	 */
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
