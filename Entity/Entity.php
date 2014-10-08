<?php
namespace Asgard\Entity;

/**
 * Entity.
 * @property integer $id
 * @author Michel Hognerud <michel@hognerud.com>
 */
abstract class Entity {
	/**
	 * Entity data.
	 * @var array
	 */
	public $data = [ #public for behaviors
		'properties'   => [],
		'translations' => [],
	];
	/**
	 * Entity definition.
	 * @var EntityDefinition
	 */
	protected $definition;
	/**
	 * Default locale.
	 * @var string
	 */
	protected $locale;

	/**
	 * Constructor.
	 * @param array  $attrs
	 * @param string $locale
	 */
	public function __construct(array $attrs=null, $locale=null) {
		$this->setLocale($locale);
		$this->loadDefault();
		if(is_array($attrs))
			$this->set($attrs);
	}

	/**
	 * Return the default locale.
	 * @return string
	 */
	public function getLocale() {
		if($this->locale === null)
			$this->locale = $this->getDefinition()->getEntitiesManager()->getDefaultLocale();
		return $this->locale;
	}

	/**
	 * Set the entity definition.
	 * @param EntityDefinition $definition
	 */
	public function setDefinition($definition) {
		$this->definition = $definition;
		return $this;
	}

	/**
	 * Set the default locale.
	 * @param string $locale
	 */
	public function setLocale($locale) {
		$this->locale = $locale;
	}

	/**
	 * __set magic method.
	 * @param string $name
	 * @param mixed  $value
	 */
	public function __set($name, $value) {
		$this->set($name, $value);
	}

	/**
	 * __get magic method.
	 * @param  string $name
	 * @return mixed
	 */
	public function __get($name) {
		return $this->get($name);
	}

	/**
	 * __isset magic method.
	 * @param  string  $name
	 * @return boolean
	 */
	public function __isset($name) {
		return isset($this->data['properties'][$name]);
	}

	/**
	 * __unset magic method.
	 * @param string $name
	 */
	public function __unset($name) {
		unset($this->data['properties'][$name]);
	}

	/**
	 * __callStatic magic method. For active-record-like entities only.
	 * @param  string $name
	 * @param  array  $arguments
	 * @return mixed
	 */
	public static function __callStatic($name, array $arguments) {
		return static::getStaticDefinition()->callStatic($name, $arguments);
	}

	/**
	 * __call magic method. For active-record-like entities only.
	 * @param  string $name
	 * @param  array  $arguments
	 * @return mixed
	 */
	public function __call($name, array $arguments) {
		return $this->getDefinition()->call($this, $name, $arguments);
	}

	/**
	 * Initialize the configuration. To be overwritten in the entity class.
	 * @param  EntityDefinition $entityDefinition
	 */
	public static function definition(EntityDefinition $entityDefinition) {}

	/**
	 * Return the definition.
	 * @return EntityDefinition
	 */
	public function getDefinition() {
		if(isset($this->definition))
			return $this->definition;
		else
			return $this->definition = static::getStaticDefinition();
	}

	/**
	 * Return a static definition, if entity used like active-record.
	 * @return EntityDefinition
	 */
	public static function getStaticDefinition() {
		#only for entities without dependency injection, activerecord like, e.g. new Article or Article::find();
		return EntitiesManager::singleton()->get(get_called_class());
	}

	/**
	 * Load default data.
	 * @return Entity
	 */
	public function loadDefault() {
		foreach($this->getDefinition()->properties() as $name=>$property)
			$this->set($name, $property->getDefault($this, $name));
		return $this;
	}

	/**
	 * Check if the entity has no id.
	 * @return boolean true if entity has no id
	 */
	public function isNew() {
		return $this->get('id') === null;
	}

	/**
	 * Check if the entity has an id.
	 * @return boolean true if entity has an id
	 */
	public function isOld() {
		return !$this->isNew();
	}

	/**
	 * Get a validator.
	 * @param  array $locales
	 * @return \Asgard\Validation\ValidatorInterface
	 */
	public function getValidator(array $locales=[]) {
		$validator = $this->getDefinition()->getEntitiesManager()->createValidator();
		return $this->prepareValidator($validator, $locales);
	}

	/**
	 * Prepare the validator.
	 * @param  \Asgard\Validation\ValidatorInterface $validator
	 * @param  array                        $locales
	 * @return \Asgard\Validation\ValidatorInterface
	 */
	public function prepareValidator(\Asgard\Validation\ValidatorInterface $validator, array $locales=[]) {
		$messages = [];

		foreach($this->getDefinition()->properties() as $name=>$property) {
			if($locales && $property->get('i18n')) {
				foreach($locales as $locale) {
					if($property->get('many')) {
						if($this->get($name) instanceof ManyCollection) {
							foreach($this->get($name) as $k=>$v) {
								$validator->attribute($name.'.'.$locale.'.'.$k, $property->getRules());
								$validator->attribute($name.'.'.$locale.'.'.$k)->formatParameters(function(&$params) use($name) {
									$params['attribute'] = $name;
								});
							}
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
					if($this->get($name) instanceof ManyCollection) {
						foreach($this->get($name) as $k=>$v) {
							$validator->attribute($name.'.'.$k, $property->getRules());
							$validator->attribute($name.'.'.$k)->formatParameters(function(&$params) use($name) {
								$params['attribute'] = $name;
							});
						}
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
	 * Check if entity is valid.
	 * @return boolean
	 */
	public function valid() {
		$data = $this->toArrayRaw();
		$validator = $this->getValidator();
		return $this->getDefinition()->trigger('validation', [$this, $validator, &$data], function($chain, $entity, $validator, &$data) {
			return $validator->valid($data);
		});
	}

	/**
	 * Return entity errors.
	 * @return array
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
	 * Hard set data. No pre-processing.
	 * @param array|string $name
	 * @param mixed        $value
	 * @param string       $locale
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
			if($this->getDefinition()->property($name)->get('i18n') && $locale !== $this->getLocale()) {
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
	 * Soft set data. With pre-processing.
	 * @param array|string $name
	 * @param mixed        $value
	 * @param string       $locale
	 * @param boolean      $hook
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

			if($this->getDefinition()->property($name)->get('i18n') && $locale !== $this->getLocale())
				$this->data['translations'][$locale][$name] = $value;
			else
				$this->data['properties'][$name] = $value;
		}
		else
			$this->data[$name] = $value;

		return $this;
	}

	/**
	 * Hard get data. With no pre-processing.
	 * @param  string       $name
	 * @param  string|array $locale
	 * @return mixed
	 */
	public function _get($name, $locale=null) {
		if(!$locale)
			$locale = $this->getLocale();

		if($this->getDefinition()->hasProperty($name)) {
			if($this->getDefinition()->property($name)->get('i18n') && $locale !== $this->getLocale()) {
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
					$this->getDefinition()->trigger('getTranslations', [$this, $name, $locale]);
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
	 * Hard get data. With pre-processing.
	 * @param  string $name
	 * @param  string|array $locale
	 * @return mixed
	 */
	public function get($name, $locale=null) {
		if(!$locale)
			$locale = $this->getLocale();

		if(($res = $this->getDefinition()->trigger('get', [$this, $name, $locale])) !== null)
			return $res;

		return $this->_get($name, $locale);
	}

	/**
	 * Convert entity to a raw array.
	 * @param  integer $depth
	 * @return array
	 */
	public function toArrayRaw($depth=0) {
		$res = [];

		foreach($this->getDefinition()->properties() as $name=>$property) {
			if($this->getDefinition()->property($name)->get('type') == 'entity') {
				if($depth < 1)
					continue;
				if($this->getDefinition()->property($name)->get('many')) {
					foreach($this->get($name) as $entity)
						$res[$name][] = $entity->toArrayRaw($depth-1);
				}
				else
					$res[$name] = $entity->toArrayRaw($depth-1);
			}
			elseif($this->getDefinition()->property($name)->get('many'))
				$res[$name] = $this->get($name)->all();
			else
				$res[$name] = $this->get($name);
		}

		return $res;
	}

	/**
	 * Convert entity to a formatted array.
	 * @param  integer $depth
	 * @return array
	 */
	public function toArray($depth=0) {
		$res = [];

		foreach($this->getDefinition()->properties() as $name=>$property) {
			if($this->getDefinition()->property($name)->get('type') == 'entity') {
				if($depth < 1)
					continue;
				if($this->getDefinition()->property($name)->get('many')) {
					foreach($this->get($name) as $entity)
						$res[$name][] = $entity->toArray($depth-1);
				}
				else
					$res[$name] = $entity->toArray($depth-1);
			}
			elseif($property->get('many')) {
				foreach($this->get($name) as $k=>$v)
					$res[$name][$k] = $this->propertyToArray($v, $property);
			}
			else
				$res[$name] = $this->propertyToArray($this->get($name), $property);
		}

		return $res;
	}

	/**
	 * Convert entity to json.
	 * @param  integer $depth
	 * @return string
	 */
	public function toJSON($depth=0) {
		return json_encode($this->toArray($depth));
	}

	/**
	 * Convert an array of entities to json.
	 * @param  array  $entities
	 * @param  integer $depth
	 * @return string
	 */
	public static function arrayToJSON(array $entities, $depth=0) {
		foreach($entities as $k=>$entity)
			$entities[$k] = $entity->toArray($depth);
		return json_encode($entities);
	}

	/**
	 * Convert a property to a strig or an array.
	 * @param  mixed    $v
	 * @param  Property $property
	 * @return string|array
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
	 * Return entity in another language.
	 * @param  string $locale
	 * @return Entity
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
	 * Get entity locales.
	 * @return array
	 */
	public function getLocales() {
		return array_merge([$this->getLocale()], array_keys($this->data['translations']));
	}

	/**
	 * Convert entity to a raw array with translations.
	 * @param  array $locales
	 * @param  integer $depth
	 * @return array
	 */
	public function toArrayRawI18N(array $locales=[], $depth=0) {
		if(!$locales)
			$locales = $this->getLocales();
		$res = [];

		foreach($this->getDefinition()->properties() as $name=>$property) {
			if($property->get('i18n')) {
				foreach($locales as $locale) {
					if($this->getDefinition()->property($name)->get('many'))
						$res[$name][$locale] = $this->get($name, $locale)->all();
					else
						$res[$name][$locale] = $this->get($name, $locale);
				}
			}
			elseif($this->getDefinition()->property($name)->get('type') == 'entity') {
				if($depth < 1)
					continue;
				if($this->getDefinition()->property($name)->get('many')) {
					foreach($this->get($name) as $entity)
						$res[$name][] = $entity->toArrayRawI18N($locales, $depth-1);
				}
				else
					$res[$name] = $entity->toArrayRawI18N($locales, $depth-1);
			}
			elseif($this->getDefinition()->property($name)->get('many'))
				$res[$name] = $this->get($name)->all();
			else
				$res[$name] = $this->get($name);
		}

		return $res;
	}

	/**
	 * Convert entity to a formatted array with translations.
	 * @param  array $locales
	 * @param  integer $depth
	 * @return array
	 */
	public function toArrayI18N(array $locales=[], $depth=0) {
		if(!$locales)
			$locales = $this->getLocales();
		$res = [];

		foreach($this->getDefinition()->properties() as $name=>$property) {
			if($property->get('i18n')) {
				foreach($locales as $locale) {
					if($this->getDefinition()->property($name)->get('many')) {
						foreach($this->get($name, $locale)->all() as $k=>$v)
							$res[$name][$locale][$k] = $this->propertyToArray($v, $property);
					}
					else
						$res[$name][$locale] = $this->propertyToArray($this->get($name, $locale), $property);
				}
			}
			elseif($this->getDefinition()->property($name)->get('type') == 'entity') {
				if($depth < 1)
					continue;
				if($this->getDefinition()->property($name)->get('many')) {
					foreach($this->get($name) as $entity)
						$res[$name][] = $entity->toArrayI18N($locales, $depth-1);
				}
				else
					$res[$name] = $entity->toArrayI18N($depth-1);
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
	 * Convert entity to JSON with translations.
	 * @param  array $locales
	 * @param  integer $depth
	 * @return string
	 */
	public function toJSONI18N(array $locales=[], $depth=0) {
		if(!$locales)
			$locales = $this->getLocales();
		return json_encode($this->toArrayI18N($locales, $depth));
	}

	/**
	 * Convert many entities to JSON with translations.
	 * @param  array  $entities
	 * @param  array $locales
	 * @param  integer $depth
	 * @return string
	 */
	public static function arrayToJSONI18N(array $entities, array $locales=[], $depth=0) {
		foreach($entities as $k=>$entity)
			$entities[$k] = $entity->toArrayI18N($locales, $depth);
		return json_encode($entities);
	}

	/**
	 * Check if entity and translations are valid.
	 * @param  array $locales
	 * @return boolean
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
	 * Return errors for entity and translations.
	 * @param  array $locales
	 * @return array
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
