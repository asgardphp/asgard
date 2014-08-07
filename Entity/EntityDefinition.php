<?php
namespace Asgard\Entity;

class EntityDefinition {
	use \Asgard\Hook\Hookable;
	use \Asgard\Container\ContainerAware;
	
	protected $entityClass;

	protected $metas = [];
	protected $properties = [];
	public $behaviors = [];
	public $messages = [];
	public $relations = [];
	protected $calls = [];
	protected $statics = [];
	protected $staticsCatchAll = [];
	protected $callsCatchAll = [];

	public function __construct($entityClass, $container) {
		$reflectionClass = new \ReflectionClass($entityClass);
		if(!$reflectionClass->IsInstantiable())
			return;

		$this->container = $container;
		$this->entityClass = $entityClass;

		$entityClass::definition($this);

		$behaviors = $this->behaviors;
		$this->behaviors = [];
		$this->loadBehaviors($behaviors);
	}

	public function __sleep() {
		return ['entityClass', 'metas', 'properties', 'behaviors', 'messages', 'relations', 'calls', 'statics', 'staticsCatchAll', 'callsCatchAll'];
	}

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

			$this->properties = [];
			foreach($properties as $name=>$property)
				$this->addProperty($name, $property);
		}
		else
			$this->metas[$name] = $value;
	}

	public function __get($name) {
		if(!isset($this->metas[$name]))
			return;
		return $this->metas[$name];
	}

	public function __isset($name) {
		return isset($this->metas[$name]);
	}

	public function __call($name, array $arguments) {
		return $this->callStatic($name, $arguments);
	}

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

	public function loadBehaviors($behaviors) {
		$this->container['hooks']->trigger('Asgard.Entity.LoadBehaviors', [&$behaviors]);

		foreach($behaviors as $behavior)
			$this->loadBehavior($behavior);
	}

	public function loadBehavior($behavior) {
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

	public function getClass() {
		return $this->entityClass;
	}

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
			$property = $this->container->make('Asgard.Entity.PropertyType.'.$type, [$property], function($params) use($type) {
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

	public function hasProperty($name) {
		return isset($this->properties[$name]);
	}

	public function property($name) {
		return $this->properties[$name];
	}

	public function properties() {
		return $this->properties;
	}

	public function propertyNames() {
		return array_keys($this->properties());
	}

	public function messages() {
		return $this->messages;
	}

	public function hasBehavior($class) {
		foreach($this->behaviors as $behavior) {
			if($behavior instanceof $class)
				return true;
		}
		return false;
	}

	public function isI18N() {
		foreach($this->properties as $prop) {
			if($prop->i18n)
				return true;
		}
		return false;
	}
	
	public function getShortName() {
		return self::basename(strtolower($this->getClass()));
	}

	private static function basename($ns) {
		return basename(str_replace('\\', DIRECTORY_SEPARATOR, $ns));
	}

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
				$value = $this->property($name)->set($value, $this, $name);
		}
	}

	public function processBeforeAdd($entity, $name, &$value, $locale=null, $hook=true) {
		if($hook)
			$this->trigger('set', [$entity, $name, &$value, $locale]);
	
		if($this->property($name)->setHook) {
			$hook = $this->property($name)->setHook;
			$value = call_user_func_array($hook, [$value]);
		}

		$value = $this->property($name)->doSet($value, $this, $name);
	}
}