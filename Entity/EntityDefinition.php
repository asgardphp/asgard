<?php
namespace Asgard\Entity;

class EntityDefinition extends \Asgard\Hook\Hookable {
	protected $entityClass;

	protected $app;
	protected $metas = array();
	protected $properties = array();
	public $behaviors = array();
	public $messages = array();
	public $relations = array();
	protected $calls = array();
	protected $statics = array();
	protected $staticsCatchAll = array();
	protected $callsCatchAll = array();

	public function __construct($entityClass, $app) {
		$reflectionClass = new \ReflectionClass($entityClass);
		if(!$reflectionClass->IsInstantiable())
			return;

		$this->app = $app;
		$this->entityClass = $entityClass;

		$entityClass::definition($this);

		$behaviors = $this->behaviors;
		$this->behaviors = array();
		$this->loadBehaviors($behaviors);
	}

	public function setApp($app) {
		$this->app = $app;
	}

	public function getApp() {
		return $this->app;
	}

	public function __sleep() {
		return array('entityClass', 'metas', 'properties', 'behaviors', 'messages', 'relations', 'calls', 'statics', 'staticsCatchAll', 'callsCatchAll');
	}

	public function __set($name, $value) {
		if($name == 'properties') {
			$properties = $value;
			$clone = $properties;
			foreach($clone as $name=>$property) {
				if(is_int($name)) {
					$properties = 
						\Asgard\Utils\Tools::array_before($properties, $name) +
						array($property => array()) +
						\Asgard\Utils\Tools::array_after($properties, $name);
				}
			}

			$this->properties = array();
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
				$res = call_user_func_array(array($behavior, 'callCatchAll'), array($entity, $name, $arguments, &$processed));
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
				$res = call_user_func_array(array($behavior, 'staticCatchAll'), array($name, $arguments, &$processed));
				if($processed)
					return $res;
			}

			if(method_exists($this, $name))
				return call_user_func_array(array($this, $name), $arguments);

			throw new \Exception('Static method '.$name.' does not exist for entity '.$this->entityClass);
		}
	}

	public function loadBehaviors($behaviors) {
		$this->app['hooks']->trigger('Asgard.Entity.LoadBehaviors', array(&$behaviors));

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
				$this->calls[str_replace('call_', '', $methodReflection->getName())] = array($behavior, $methodReflection->getName());
			elseif(strpos($methodReflection->getName(), 'static_') === 0)
				$this->statics[str_replace('static_', '', $methodReflection->getName())] = array($behavior, $methodReflection->getName());
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
			$property = array('type'=>$property);
		if(is_array($property)) {
			foreach($property as $k=>$v) {
				if(is_int($k)) {
					unset($property[$k]);
					$property[$v] = true;
				}
			}

			if(!isset($property['type']) || !$property['type']) {
				if(isset($property['multiple']) && $property['multiple'])
					$property['type'] = 'array';
				else
					$property['type'] = 'text';
			}

			$property = $this->app->make('Asgard.Entity.PropertyType', array($property['type'], $property), function($type, $params) {
				$class = '\Asgard\Entity\Properties\\'.ucfirst($type).'Property';
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
		return static::basename(strtolower($this->getClass()));
	}

	private static function basename($ns) {
		return basename(str_replace('\\', DIRECTORY_SEPARATOR, $ns));
	}
}