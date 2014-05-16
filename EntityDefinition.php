<?php
namespace Asgard\Entity;

class EntityDefinition extends \Asgard\Hook\Hookable {
	protected $entityClass;

	protected $metas = array();
	protected $properties = array();
	public $behaviors = array();
	public $messages = array();
	public $relations = array();
	protected $calls = array();
	protected $statics = array();
	protected $staticsCatchAll = array();
	protected $callsCatchAll = array();

	public function __construct($entityClass) {
		$reflectionClass = new \ReflectionClass($entityClass);
		if(!$reflectionClass->IsInstantiable())
			return;

		$this->entityClass = $entityClass;

		$entityClass::definition($this);

		$this->loadBehaviors();
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

	public function loadBehaviors() {
		\Asgard\Core\App::get('hook')->trigger('behaviors_pre_load', array($this));

		#not using foreach because new behaviors may be added in the loop
		for($i=0; $i<count($this->behaviors); $i++) {
			if(!$this->behaviors[$i] instanceof \Asgard\Entity\Behavior)
				throw new \Exception($this->entityClass.' has an invalid behavior object.');
			$this->behaviors[$i]->setDefinition($this);
			$this->behaviors[$i]->load($this);

			$reflection = new \ReflectionClass($this->behaviors[$i]);
			foreach($reflection->getMethods() as $methodReflection) {
				if(strpos($methodReflection->getName(), 'call_') === 0)
					$this->calls[str_replace('call_', '', $methodReflection->getName())] = array($this->behaviors[$i], $methodReflection->getName());
				elseif(strpos($methodReflection->getName(), 'static_') === 0)
					$this->statics[str_replace('static_', '', $methodReflection->getName())] = array($this->behaviors[$i], $methodReflection->getName());
				elseif($methodReflection->getName() === 'staticCatchAll')
					$this->staticsCatchAll[] = $this->behaviors[$i];
				elseif($methodReflection->getName() === 'callCatchAll')
					$this->callsCatchAll[] = $this->behaviors[$i];
			}
		}
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

			$propertyClass = \Asgard\Core\App::get('hook')->trigger('entity_property_type', array($property['type']), function($chain, $type) {
				return '\Asgard\Entity\Properties\\'.ucfirst($type).'Property';
			});

			$property = new $propertyClass($property);
		}

		if(is_object($property)) {
			$property->setEntity($this->entityClass);
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

	public function addBehavior(Behavior $behavior) {
		if(!$this->hasBehavior($behavior))
			$this->behaviors[] = $behavior;
	}

	public function isI18N() {
		foreach($this->properties as $prop) {
			if($prop->i18n)
				return true;
		}
		return false;
	}
	
	public function getShortName() {
		return \Asgard\Utils\NamespaceUtils::basename(strtolower($this->getClass()));
	}
}