<?php
namespace Asgard\Entity;

/**
 * Entity definition.
 * @property-write array $properties
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Definition {
	use \Asgard\Hook\HookableTrait;

	/**
	 * Entities manager dependency.
	 * @var EntityManagerInterface
	 */
	protected $entityManager;
	/**
	 * General hooks manager dependency.
	 * @var \Asgard\Hook\HookManagerInterface
	 */
	protected $generalHookManager;
	/**
	 * Entity class.
	 * @var string
	 */
	protected $entityClass;
	/**
	 * Behaviors.
	 * @var array
	 */
	public $behaviors = [];
	/**
	 * Validation messages.
	 * @var array
	 */
	public $messages = [];
	/**
	 * Meta data.
	 * @var array
	 */
	protected $metas = [];
	/**
	 * Entity properties.
	 * @var array
	 */
	protected $properties = [];
	/**
	 * Calls callbacks.
	 * @var array
	 */
	protected $calls = [];
	/**
	 * Statics callbacks.
	 * @var array
	 */
	protected $statics = [];
	/**
	 * Statics catch all.
	 * @var array
	 */
	protected $staticsCatchAll = [];
	/**
	 * Calls catch all.
	 * @var array
	 */
	protected $callsCatchAll = [];

	/**
	 * Constructor.
	 * @param string                    $entityClass
	 * @param EntityManagerInterface           $entityManager
	 * @param \Asgard\Hook\HookManagerInterface $generalHookManager
	 */
	public function __construct($entityClass, EntityManagerInterface $entityManager, \Asgard\Hook\HookManagerInterface $generalHookManager=null) {
		$reflectionClass = new \ReflectionClass($entityClass);
		if(!$reflectionClass->IsInstantiable())
			return;

		$this->entityClass = $entityClass;
		$this->entityManager = $entityManager;
		$this->generalHookManager = $generalHookManager;

		$this->addProperty('id', [
			'type'     => 'text',
			'editable' => false,
			'required' => false,
			'position' => -9999,
			'defaut'   => 0,
			'orm'      => [
				'type'           => 'int(11)',
				'auto_increment' => true,
				'key'            => 'PRIMARY',
				'nullable'       => false,
			],
		]);

		$entityClass::definition($this);

		if($generalHookManager !== null)
			$generalHookManager->trigger('Asgard.Entity.Definition', [$this]);

		$behaviors = $this->behaviors;
		$this->behaviors = [];
		$this->loadBehaviors($behaviors);
	}

	/**
	 * Return the services container.
	 * @return \Asgard\Container\ContainerInterface
	 */
	public function getContainer() {
		return $this->entityManager->getContainer();
	}

	/**
	 * __sleep magic method.
	 * @return array
	 */
	public function __sleep() {
		return ['entityClass', 'metas', 'properties', 'behaviors', 'messages', 'calls', 'statics', 'staticsCatchAll', 'callsCatchAll'];
	}

	/**
	 * __set magic method.
	 * @param string $name
	 * @param mixed  $value
	 */
	public function __set($name, $value) {
		if($name == 'properties') {
			$properties = $value;
			$clone = $properties;
			foreach($clone as $name=>$property) {
				if(is_int($name)) {
					$properties =
						\Asgard\Common\ArrayUtils::before($properties, $name) +
						[$property => []] +
						\Asgard\Common\ArrayUtils::after($properties, $name);
				}
			}

			foreach($properties as $name=>$property)
				$this->addProperty($name, $property);
		}
		else
			$this->set($name, $value);
	}

	/**
	 * __call magic method. For behaviors static methods.
	 * @param  string $name
	 * @param  array  $arguments
	 * @return mixed
	 */
	public function __call($name, array $arguments) {
		return $this->callStatic($name, $arguments);
	}

	/**
	 * Handle a custom call.
	 * @param  Entity $entity
	 * @param  string $name
	 * @param  array  $arguments
	 * @throws \Exception If method name does not exist.
	 * @return mixed
	 */
	public function call(Entity $entity, $name, array $arguments) {
		if(isset($this->calls[$name])) {
			array_unshift($arguments, $entity);
			return call_user_func_array($this->calls[$name], $arguments);
		}
		else {
			foreach($this->callsCatchAll as $behavior) {
				$processed = false;
				$res = call_user_func_array([$behavior, 'callCatchAll'], [$entity, $name, $arguments, &$processed]);
				if($processed)
					return $res;
			}

			try {
				return $this->callStatic($name, $arguments);
			} catch(\Exception $e) {
				throw new \Exception('Method '.$name.' does not exist for entity '.$this->entityClass);
			}
		}
	}

	/**
	 * Handle a custom static call.
	 * @param  string $name
	 * @param  array  $arguments
	 * @return mixed
	 */
	public function callStatic($name, array $arguments) {
		if(isset($this->statics[$name]))
			return call_user_func_array($this->statics[$name], $arguments);
		else {
			foreach($this->staticsCatchAll as $behavior) {
				$processed = false;
				$res = call_user_func_array([$behavior, 'staticCatchAll'], [$name, $arguments, &$processed]);
				if($processed)
					return $res;
			}

			if(method_exists($this, $name))
				return call_user_func_array([$this, $name], $arguments);

			throw new \Exception('Static method '.$name.' does not exist for entity '.$this->entityClass);
		}
	}

	/**
	 * Set the entityManager dependency.
	 * @param EntityManagerInterface $entityManager
	 * @return Definition $this
	 */
	public function setEntityManager(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
		return $this;
	}

	/**
	 * Set the general hooks manager dependency.
	 * @param \Asgard\Hook\HookManagerInterface $generalHookManager
	 * @return Definition $this
	 */
	public function setGeneralHookManager($generalHookManager) {
		$this->generalHookManager = $generalHookManager;
		return $this;
	}

	/**
	 * Return the entityManager dependency.
	 * @return EntityManagerInterface
	 */
	public function getEntityManager() {
		return $this->entityManager;
	}

	/**
	 * Load the bahaviors.
	 * @param  array $behaviors
	 */
	public function loadBehaviors($behaviors) {
		if($this->generalHookManager !== null)
			$this->generalHookManager->trigger('Asgard.Entity.LoadBehaviors', [$this, &$behaviors]);

		foreach($behaviors as $behavior)
			$this->loadBehavior($behavior);
	}

	/**
	 * Load a behavior.
	 * @param  mixed $behavior
	 */
	public function loadBehavior($behavior) {
		if(!is_object($behavior))
			return;
		if(!$behavior instanceof \Asgard\Entity\Behavior)
			throw new \Exception($this->entityClass.' has an invalid behavior object.');
		$behavior->setDefinition($this);
		$behavior->load($this);

		$reflection = new \ReflectionClass($behavior);
		foreach($reflection->getMethods() as $methodReflection) {
			if(strpos($methodReflection->getName(), 'call_') === 0)
				$this->calls[str_replace('call_', '', $methodReflection->getName())] = [$behavior, $methodReflection->getName()];
			elseif(strpos($methodReflection->getName(), 'static_') === 0)
				$this->statics[str_replace('static_', '', $methodReflection->getName())] = [$behavior, $methodReflection->getName()];
			elseif($methodReflection->getName() === 'staticCatchAll')
				$this->staticsCatchAll[] = $behavior;
			elseif($methodReflection->getName() === 'callCatchAll')
				$this->callsCatchAll[] = $behavior;
		}

		$this->behaviors[] = $behavior;
	}

	/**
	 * Return the entity class.
	 * @return string
	 */
	public function getClass() {
		return $this->entityClass;
	}

	/**
	 * Add a property.
	 * @param string                $name
	 * @param string|array|Property $property
	 */
	public function addProperty($name, $property=null) {
		if($property === null)
			$property = 'text';
		if(is_string($property))
			$property = ['type'=>$property];
		if(is_array($property)) {
			foreach($property as $k=>$v) {
				if(is_int($k)) {
					unset($property[$k]);
					$property[$v] = true;
				}
			}

			if(!isset($property['type']) || !$property['type'])
				$property['type'] = 'text';

			$type = $property['type'];
			$property = $this->getContainer()->make('Asgard.Entity.PropertyType.'.$type, [$property], function($params) use($type) {
				$class = '\Asgard\Entity\Properties\\'.ucfirst(strtolower($type)).'Property';
				return new $class($params);
			});
		}

		$property->setDefinition($this);
		$property->setName($name);

		if($property->getPosition() === null)
			$property->setPosition(count($this->properties)+1);
		$this->properties[$name] = $property;

		uasort($this->properties, function($a, $b) {
			return $a->getPosition() > $b->getPosition();
		});
	}

	/**
	 * Check if definition has a property.
	 * @param  string  $name
	 * @return boolean
	 */
	public function hasProperty($name) {
		return isset($this->properties[$name]);
	}

	/**
	 * Return a property.
	 * @param  string $name
	 * @return Property
	 */
	public function property($name) {
		return $this->properties[$name];
	}

	/**
	 * Return all properties.
	 * @return array
	 */
	public function properties() {
		return $this->properties;
	}

	/**
	 * Return all validation messages.
	 * @return array
	 */
	public function messages() {
		return $this->messages;
	}

	/**
	 * Check if has a behavior.
	 * @param  string  $class
	 * @return boolean
	 */
	public function hasBehavior($class) {
		foreach($this->behaviors as $behavior) {
			if($behavior instanceof $class)
				return true;
		}
		return false;
	}

	/**
	 * Set a meta data.
	 * @param string $name
	 * @param mixed  $value
	 */
	public function set($name, $value) {
		$this->metas[$name] = $value;
		return $this;
	}

	/**
	 * Return a meta data.
	 * @param  string $name
	 * @return mixed
	 */
	public function get($name) {
		if(isset($this->metas[$name]))
			return $this->metas[$name];
	}

	/**
	 * Check if contains i18n properties.
	 * @return boolean
	 */
	public function isI18N() {
		foreach($this->properties as $prop) {
			if($prop->get('i18n'))
				return true;
		}
		return false;
	}

	/**
	 * Get the entity short name.
	 * @return string
	 */
	public function getShortName() {
		return basename(str_replace('\\', DIRECTORY_SEPARATOR, strtolower($this->getClass())));
	}

	/**
	 * Process values before entity set.
	 * @param  Entity  $entity
	 * @param  string  $name
	 * @param  mixed   $value
	 * @param  string  $locale
	 * @param  boolean $hook   True to enable hooks.
	 */
	public function processBeforeSet($entity, $name, &$value, $locale=null, $hook=true) {
		if($hook)
			$this->trigger('set', [$entity, $name, &$value, $locale]);

		if($this->hasProperty($name)) {
			if($this->property($name)->get('setHook')) {
				$hook = $this->property($name)->get('setHook');
				$value = call_user_func_array($hook, [$value]);
			}

			if($this->property($name)->get('i18n')) {
				if($locale == 'all') {
					$val = [];
					foreach($value as $one => $v)
						$val[$one] = $this->property($name)->setDecorator($v, $entity, $name);
					$value = $val;
				}
				else
					$value = $this->property($name)->setDecorator($value, $entity, $name);
			}
			else
				$value = $this->property($name)->setDecorator($value, $entity, $name);
		}
	}

	/**
	 * Process values before adding to a ManyCollection.
	 * @param  Entity  $entity
	 * @param  string  $name
	 * @param  mixed   $value
	 * @param  string  $locale
	 * @param  boolean $hook   True to enable hooks.
	 */
	public function processBeforeAdd($entity, $name, &$value, $locale=null, $hook=true) {
		if($hook)
			$this->trigger('set', [$entity, $name, &$value, $locale]);

		if($this->property($name)->get('setHook')) {
			$hook  = $this->property($name)->get('setHook');
			$value = call_user_func_array($hook, [$value]);
		}

		$value = $this->property($name)->doSet($value, $entity, $name);
	}

	/**
	 * Make a new entity.
	 * @param  array  $attrs
	 * @param  string $locale
	 * @return Entity
	 */
	public function make(array $attrs=null, $locale=null) {
		$entityClass = $this->entityClass;
		$entity      = new $entityClass($attrs, $locale);
		$entity->setEntityManager($this->entityManager);
		return $entity;
	}
}