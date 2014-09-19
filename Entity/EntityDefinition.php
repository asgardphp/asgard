<?php
namespace Asgard\Entity;

/**
 * 
 */
class EntityDefinition {
	use \Asgard\Hook\HookableTrait;
	
	/**
	 * [$entitiesManager description]
	 * @var [type]
	 */
	protected $entitiesManager;
	/**
	 * [$generalHooksManager description]
	 * @var [type]
	 */
	protected $generalHooksManager;
	/**
	 * [$entityClass description]
	 * @var [type]
	 */
	protected $entityClass;
	/**
	 * [$behaviors description]
	 * @var [type]
	 */
	public $behaviors = [];
	/**
	 * [$messages description]
	 * @var [type]
	 */
	public $messages = [];
	/**
	 * [$metas description]
	 * @var [type]
	 */
	protected $metas = [];
	/**
	 * [$properties description]
	 * @var [type]
	 */
	protected $properties = [];
	/**
	 * [$calls description]
	 * @var [type]
	 */
	protected $calls = [];
	/**
	 * [$statics description]
	 * @var [type]
	 */
	protected $statics = [];
	/**
	 * [$staticsCatchAll description]
	 * @var [type]
	 */
	protected $staticsCatchAll = [];
	/**
	 * [$callsCatchAll description]
	 * @var [type]
	 */
	protected $callsCatchAll = [];

	/**
	 * [__construct description]
	 * @param [type] $entityClass
	 * @param [type] $entitiesManager
	 * @param [type] $generalHooksManager
	 */
	public function __construct($entityClass, $entitiesManager, $generalHooksManager=null) {
		$reflectionClass = new \ReflectionClass($entityClass);
		if(!$reflectionClass->IsInstantiable())
			return;

		$this->entityClass = $entityClass;
		$this->entitiesManager = $entitiesManager;
		$this->generalHooksManager = $generalHooksManager;

		$this->addProperty('id', [
			'type'     => 'text', 
			'editable' => false, 
			'required' => false,
			'position' => -9999,
			'defaut'   => 0,
			'orm'      => [
				'type'           => 'int(11)',
				'auto_increment' => true,
				'key'            => 'PRI',
				'nullable'       => false,
			],
		]);

		$entityClass::definition($this);

		$behaviors = $this->behaviors;
		$this->behaviors = [];
		$this->loadBehaviors($behaviors);
	}

	/**
	 * [getContainer description]
	 * @return [type]
	 */
	public function getContainer() {
		return $this->entitiesManager->getContainer();
	}

	/**
	 * [__sleep description]
	 * @return array
	 */
	public function __sleep() {
		return ['entityClass', 'metas', 'properties', 'behaviors', 'messages', 'calls', 'statics', 'staticsCatchAll', 'callsCatchAll'];
	}

	/**
	 * [__set description]
	 * @param [type] $name
	 * @param [type] $value
	 */
	public function __set($name, $value) {
		if($name == 'properties') {
			$properties = $value;
			$clone = $properties;
			foreach($clone as $name=>$property) {
				if(is_int($name)) {
					$properties = 
						\Asgard\Common\ArrayUtils::array_before($properties, $name) +
						[$property => []] +
						\Asgard\Common\ArrayUtils::array_after($properties, $name);
				}
			}

			foreach($properties as $name=>$property)
				$this->addProperty($name, $property);
		}
		else
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
		return isset($this->metas[$name]);
	}

	/**
	 * [__call description]
	 * @param  [type] $name
	 * @param  array  $arguments
	 * @return [type]
	 */
	public function __call($name, array $arguments) {
		return $this->callStatic($name, $arguments);
	}

	/**
	 * [call description]
	 * @param  Entity $entity
	 * @param  [type] $name
	 * @param  array  $arguments
	 * @return [type]
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
	 * [callStatic description]
	 * @param  [type] $name
	 * @param  array  $arguments
	 * @return [type]
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
	 * [setEntitiesManager description]
	 * @param [type] $entitiesManager
	 */
	public function setEntitiesManager($entitiesManager) {
		$this->entitiesManager = $entitiesManager;
		return $this;
	}

	/**
	 * [setGeneralHooksManager description]
	 * @param [type] $generalHooksManager
	 */
	public function setGeneralHooksManager($generalHooksManager) {
		$this->generalHooksManager = $generalHooksManager;
		return $this;
	}

	/**
	 * [getEntitiesManager description]
	 * @return [type]
	 */
	public function getEntitiesManager() {
		return $this->entitiesManager;
	}

	/**
	 * [loadBehaviors description]
	 * @param  [type] $behaviors
	 * @return [type]
	 */
	public function loadBehaviors($behaviors) {
		$this->generalHooksManager->trigger('Asgard.Entity.LoadBehaviors', [&$behaviors]);

		foreach($behaviors as $behavior)
			$this->loadBehavior($behavior);
	}

	/**
	 * [loadBehavior description]
	 * @param  [type] $behavior
	 * @return [type]
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
	 * [getClass description]
	 * @return [type]
	 */
	public function getClass() {
		return $this->entityClass;
	}

	/**
	 * [addProperty description]
	 * @param [type] $name
	 * @param [type] $property
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

		if(is_object($property)) {
			$property->setDefinition($this);
			$property->setName($name);

			if($property->getPosition() === null)
				$property->setPosition(count($this->properties)+1);
			$this->properties[$name] = $property;

			uasort($this->properties, function($a, $b) {
				if(!is_object($a) || $a->getPosition() === null)
					return 1;
				if(!is_object($b) || $b->getPosition() === null)
					return -1;
				return $a->getPosition() > $b->getPosition();
			});
		}
	}

	/**
	 * [hasProperty description]
	 * @param  [type]  $name
	 * @return boolean
	 */
	public function hasProperty($name) {
		return isset($this->properties[$name]);
	}

	/**
	 * [property description]
	 * @param  [type] $name
	 * @return [type]
	 */
	public function property($name) {
		return $this->properties[$name];
	}

	/**
	 * [properties description]
	 * @return [type]
	 */
	public function properties() {
		return $this->properties;
	}

	/**
	 * [propertyNames description]
	 * @return [type]
	 */
	public function propertyNames() {
		return array_keys($this->properties());
	}

	/**
	 * [messages description]
	 * @return [type]
	 */
	public function messages() {
		return $this->messages;
	}

	/**
	 * [hasBehavior description]
	 * @param  [type]  $class
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
	 * [set description]
	 * @param [type] $name
	 * @param [type] $value
	 */
	public function set($name, $value) {
		$this->metas[$name] = $value;
		return $this;
	}

	/**
	 * [get description]
	 * @param  [type] $k
	 * @return [type]
	 */
	public function get($k) {
		if(isset($this->metas[$k]))
			return $this->metas[$k];
	}

	/**
	 * [isI18N description]
	 * @return boolean
	 */
	public function isI18N() {
		foreach($this->properties as $prop) {
			if($prop->i18n)
				return true;
		}
		return false;
	}
	
	/**
	 * [getShortName description]
	 * @return [type]
	 */
	public function getShortName() {
		return self::basename(strtolower($this->getClass()));
	}

	/**
	 * [basename description]
	 * @param  [type] $ns
	 * @return [type]
	 */
	private static function basename($ns) {
		return basename(str_replace('\\', DIRECTORY_SEPARATOR, $ns));
	}

	/**
	 * [processBeforeSet description]
	 * @param  [type]  $entity
	 * @param  [type]  $name
	 * @param  [type]  $value
	 * @param  [type]  $locale
	 * @param  boolean $hook
	 * @return [type]
	 */
	public function processBeforeSet($entity, $name, &$value, $locale=null, $hook=true) {
		if($hook)
			$this->trigger('set', [$entity, $name, &$value, $locale]);
	
		if($this->hasProperty($name)) {
			if($this->property($name)->setHook) {
				$hook = $this->property($name)->setHook;
				$value = call_user_func_array($hook, [$value]);
			}

			if($this->property($name)->i18n) {
				if($locale == 'all') {
					$val = [];
					foreach($value as $one => $v)
						$val[$one] = $this->property($name)->set($v, $this, $name);
					$value = $val;
				}
				else
					$value = $this->property($name)->set($value, $this, $name);
			}
			else
				$value = $this->property($name)->set($value, $entity, $name);
		}
	}

	/**
	 * [processBeforeAdd description]
	 * @param  [type]  $entity
	 * @param  [type]  $name
	 * @param  [type]  $value
	 * @param  [type]  $locale
	 * @param  boolean $hook
	 * @return [type]
	 */
	public function processBeforeAdd($entity, $name, &$value, $locale=null, $hook=true) {
		if($hook)
			$this->trigger('set', [$entity, $name, &$value, $locale]);
	
		if($this->property($name)->setHook) {
			$hook  = $this->property($name)->setHook;
			$value = call_user_func_array($hook, [$value]);
		}

		$value = $this->property($name)->doSet($value, $entity, $name);
	}

	/**
	 * [make description]
	 * @param  [type] $params
	 * @param  [type] $locale
	 * @return [type]
	 */
	public function make(array $params=null, $locale=null) {
		$entityClass = $this->entityClass;
		$entity      = new $entityClass($params, $locale);
		$entity->setDefinition($this);

		return $entity;
	}
}