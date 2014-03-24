<?php
namespace Asgard\Core;

class EntityDefinition extends \Asgard\Hook\Hookable {
	protected $entityClass;

	public $meta = array();
	public $properties = array();
	public $behaviors = array();
	public $relations = array();
	public $messages = array();

	function __construct($entityClass) {
		$reflectionClass = new \ReflectionClass($entityClass);
		if(!$reflectionClass->IsInstantiable())
			return;

		$this->entityClass = $entityClass;

		$this->relations = isset($entityClass::$relations) ? $entityClass::$relations:array();
		$this->meta = isset($entityClass::$meta) ? $entityClass::$meta:array();
		$this->messages = isset($entityClass::$messages) ? $entityClass::$messages:array();

		if(isset($entityClass::$behaviors)) {
			foreach($entityClass::$behaviors as $name=>$params) {
				if(is_int($name)) {
					$name = $params;
					$params = true;
				}
				$this->behaviors[$name] = $params;
			}
		}

		$this->loadBehaviors();

		$properties = $entityClass::$properties;
		$clone = $properties;
		foreach($clone as $k=>$v) {
			if(is_int($k)) {
				$properties = 
					\Asgard\Utils\Tools::array_before($properties, $k) +
					array($v => array()) +
					\Asgard\Utils\Tools::array_after($properties, $k);
			}
		}
		foreach($properties as $k=>$params)
			$this->addProperty($k, $params);

		$entityClass::configure($this);
	}

	public function loadBehaviors() {
		$entityClass = $this->entityClass;
		\Asgard\Core\App::get('hook')->trigger('behaviors_pre_load', $this);

		#not using foreach because new behaviors may be added in the loop
		for($i=0; $i<sizeof($this->behaviors); $i++) {
			$class = array_keys($this->behaviors)[$i];
			$params = array_values($this->behaviors)[$i];
			if($params)
				$class::load($this, $params);
		}
	}

	public function __call($name, $arguments) {
		$chain = new HookChain();
		$chain->found = false;
		$res = $this->triggerChain($chain, 'callStatic', array($name, $arguments));
		if(!$chain->found)
			throw new \Exception('Static method '.$name.' does not exist for Entity '.$this->getClass());

		return $res;
	}

	public function getClass() {
		return $this->entityClass;
	}

	public function addProperty($property, $params=null) {
		if($params === null)
			$params = 'text';
		if(is_string($params))
			$params = array('type'=>$params);
		foreach($params as $k=>$v) {
			if(is_int($k)) {
				unset($params[$k]);
				$params[$v] = true;
			}
		}
		if(!isset($params['type']) || !$params['type']) {
			if(isset($params['multiple']) && $params['multiple'])
				$params['type'] = 'array';
			else
				$params['type'] = 'text';
		}

		$propertyClass = $this->trigger('propertyClass', array($params['type']), function($chain, $type) {
			return '\Asgard\Core\Properties\\'.ucfirst($type).'Property';
		});

		$this->properties[$property] = new $propertyClass($this->entityClass, $property, $params);
		// if($property == 'created_at')
		// 	d($this->properties, count($this->properties));
		if(!isset($this->properties[$property]->params['position']))
			$this->properties[$property]->params['position'] = count($this->properties);
		uasort($this->properties, function($a, $b) {
			if($a->position === null)
				return 1;
			if($b->position === null)
				return -1;
			return $a->position > $b->position;
		});
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

	public function metas() {
		return $this->meta;
	}

	public function meta($name) {
		if(isset($this->meta[$name]))
			return $this->meta[$name];
		return null;
	}

	public function messages() {
		return $this->messages;
	}

	public function behaviors() {
		return $this->behaviors;
	}

	public function behavior($name) {
		return $this->behaviors[$name];
	}

	public function hasBehavior($name) {
		return isset($this->behaviors[$name]) && $this->behaviors[$name];
	}

	public function relations() {
		return $this->relations;
	}

	public function relation($name) {
		return $this->relations[$name];
	}

	public function hasRelation($name) {
		return isset($this->relations[$name]);
	}

	public function isI18N() {
		foreach($this->properties as $prop)
			if($prop->i18n)
				return true;
		return false;
	}
	
	public function getEntityName() {
		$class = $this->getClass();
		return $class::getEntityName();
	}

	public function addMethod($method_name, $cb) {
		$this->hookOn('call', function($chain, $entity, $name, $args) use($method_name, $cb) {
			if($name == $method_name) {
				$chain->found = true;
				$chain->stop();
				return call_user_func_array($cb, array_merge(array($entity), $args));
			}
		});
	}

	public function addStaticMethod($method_name, $cb) {
		$this->hookOn('callStatic', function($chain, $name, $args) use($method_name, $cb) {
			if($name == $method_name) {
				$chain->found = true;
				return call_user_func_array($cb, $args);
			}
		});
	}
}