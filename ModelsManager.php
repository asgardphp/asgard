<?php
namespace Coxis\Core;

class ModelDefinition extends Hookable {
	protected $modelClass;

	public $meta = array();
	public $properties = array();
	public $behaviors = array();
	public $relations = array();
	public $messages = array();

	function __construct($modelClass) {
		$reflectionClass = new \ReflectionClass($modelClass);
		if(!$reflectionClass->IsInstantiable())
			return;

		$this->modelClass = $modelClass;

		$this->relations = isset($modelClass::$relations) ? $modelClass::$relations:array();
		$this->meta = isset($modelClass::$meta) ? $modelClass::$meta:array();
		$this->messages = isset($modelClass::$messages) ? $modelClass::$messages:array();

		if(isset($modelClass::$behaviors)) {
			foreach($modelClass::$behaviors as $name=>$params) {
				if(is_int($name)) {
					$name = $params;
					$params = true;
				}
				$this->behaviors[$name] = $params;
			}
		}

		$this->loadBehaviors();

		$properties = $modelClass::$properties;
		$clone = $properties;
		foreach($clone as $k=>$v) {
			if(is_int($k)) {
				$properties = 
					\Coxis\Utils\Tools::array_before($properties, $k) +
					array($v => array()) +
					\Coxis\Utils\Tools::array_after($properties, $k);
			}
		}
		foreach($properties as $k=>$params)
			$this->addProperty($k, $params);
		
		$modelClass::configure($this);
	}

	public function loadBehaviors() {
		$modelClass = $this->modelClass;
		\Hook::trigger('behaviors_pre_load', $this);

		foreach($this->behaviors as $behavior => $params)
			if($params)
				\Hook::trigger('behaviors_load_'.$behavior, $this);
	}

	public function __call($name, $arguments) {
		$chain = new HookChain();
		$chain->found = false;
		$res = $this->triggerChain($chain, 'callStatic', array($name, $arguments));
		if(!$chain->found)
			throw new \Exception('Static method '.$name.' does not exist for model '.$this->getClass());

		return $res;
	}

	public function getClass() {
		return $this->modelClass;
	}

	public function addProperty($property, $params) {
		if(is_string($params))
			$params = array('type'=>$params);
		foreach($params as $k=>$v) {
			if(is_int($k)) {
				unset($params[$k]);
				$params[$v] = true;
			}
		}
		#todo multiple values - not atomic.. ?
		if(!isset($params['type']) || !$params['type']) {
			if(isset($params['multiple']) && $params['multiple'])
				$params['type'] = 'array';
			else
				$params['type'] = 'text';
		}

		$propertyClass = $this->trigger('propertyClass', array($params['type']), function($chain, $type) {
			return '\Coxis\Core\Properties\\'.ucfirst($type).'Property';
		});

		$this->properties[$property] = new $propertyClass($this->modelClass, $property, $params);
		if(!isset($this->properties[$property]->params['position']))
			$this->properties[$property]->params['position'] = count($this->properties);
		uasort($this->properties, function($a, $b) {
			if($a->position===null)
				return 1;
			if($b->position===null)
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
	
	public function getModelName() {
		$class = $this->getClass();
		return $class::getModelName();
	}

	public function addMethod($method_name, $cb) {
		$this->hookOn('call', function($chain, $model, $name, $args) use($method_name, $cb) {
			if($name == $method_name) {
				$chain->found = true;
				$chain->stop();
				return call_user_func_array($cb, array_merge(array($model), $args));
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

class ModelsManager {
	protected $models = array();

	public function get($modelClass) {
		if(!isset($this->models[$modelClass])) {
			#is caching very useful here?
			if(\Config::get('phpcache') && $md=Cache::get('modelsmanager/'.$modelClass.'/definition'))
				$this->models[$modelClass] = $md;
			else {
				$this->models[$modelClass] = new ModelDefinition($modelClass);
				if(\Config::get('phpcache'))
					Cache::set('modelsmanager/'.$modelClass.'/definition', $this->models[$modelClass]);
			}
		}
		
		return $this->models[$modelClass];
	}
}
