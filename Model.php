<?php
namespace Coxis\Core;

class ModelException extends \Exception {
	public $errors = array();
}

abstract class Model {
	#public for behaviors
	public $data = array(
		'properties'	=>	array(),
	);

	public function __construct($param='') {
		$chain = new HookChain();
		$chain->found = false;
		$this->triggerChain($chain, 'construct', array($this, $param));
		if(!$chain->found) {
			if(is_array($param))
				$this->loadDefault()->set($param);
			else
				$this->loadDefault();
		}
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

	public static function __callStatic($name, $arguments) {
		$chain = new HookChain();
		$chain->found = false;
		$res = static::triggerChain($chain, 'callStatic', array($name, $arguments));
		if(!$chain->found)
			throw new \Exception('Static method '.$name.' does not exist for model '.static::getModelName());

		return $res;
	}

	public function __call($name, $arguments) {
		$chain = new HookChain;
		$chain->found = false;
		$res = static::triggerChain($chain, 'call', array($this, $name, $arguments));
		if(!$chain->found) {
			try {
				return static::__callStatic($name, $arguments);
			} catch(\Exception $e) {
				throw new \Exception('Method '.$name.' does not exist for model '.static::getModelName());
			}
		}

		return $res;
	}
	
	/* INIT AND MODEL CONFIGURATION */
	public static function configure($modelDefinition) {}

	public static function getDefinition() {
		return \ModelsManager::get(get_called_class());
	}

	public function loadDefault() {
		foreach(static::properties() as $name=>$property)
			$this->set($name, $property->getDefault($this));
				
		return $this;
	}

	/* PERSISTENCY */
	public function save($values=null, $force=false) {
		#set $values if any
		if($values)
			$this->set($values);
		
		#todo use model hooks
		foreach(static::getDefinition()->behaviors() as $behavior => $params)
			if($params)
				\Hook::trigger('behaviors_presave_'.$behavior, $this);
		
		if(!$force) {
			#validate params and files
			if($errors = $this->errors()) {
				$msg = implode("\n", \Coxis\Utils\Tools::flateArray($errors));
				$e = new ModelException($msg);
				$e->errors = $errors;
				throw $e;
			}
		}
		
		$chain = new HookChain;
		$this->triggerChain($chain, 'save', array($this));
		if(!$chain->executed)
			throw new \Exception('Cannot save non-persistent models');

		return $this;
	}
	
	public function destroy() {
		$chain = new HookChain;
		$this->triggerChain($chain, 'destroy', array($this));
		if(!$chain->executed)
			throw new \Exception('Cannot destroy non-persistent models');
	}

	public static function create($values=array(), $force=false) {
		$m = new static;
		return $m->save($values, $force);
	}
	
	/* VALIDATION */
	public function getValidator() {
		$constrains = array();
		$model = $this;
		$this->trigger('constrains', array(&$constrains), function($chain, &$constrains) use($model) {
			foreach($model->getDefinition()->properties as $name=>$property)
				$constrains[$name] = $property->getRules();
		});
		
		$messages = static::getDefinition()->messages();
		
		$validator = new \Coxis\Validation\Validator($constrains, $messages);
		$validator->model = $this;

		return $validator;
	}
	
	public function valid() {
		return !$this->errors();
	}
	
	public function errors() {
		#before validation
				
		$data = $this->toArrayRaw();

		$errors = null;
		$this->trigger('validation', array($this, &$data, &$errors), function($chain, $model, &$data, &$errors) {
			$errors = $model->getValidator()->errors($data);
		});
				
		return $errors;
	}

	/* ACCESSORS */
	public function set($name, $value=null, $lang=null, $raw=false) {
		if(is_array($name)) {
			$raw = $lang;
			$lang = $value;
			$vars = $name;
			foreach($vars as $k=>$v)
				$this->set($k, $v, $lang, $raw);
		}
		else {
			if(static::getDefinition()->hasProperty($name)) {
				if(!$raw && static::getDefinition()->property($name)->setHook) {
					$hook = static::getDefinition()->property($name)->setHook;
					$value = call_user_func_array($hook, array($value));
				}

				if(static::getDefinition()->property($name)->i18n) {
					if(!$lang)
						$lang = \Config::get('locale');
					if($lang == 'all')
						foreach($value as $one => $v)
							if($raw)
								$this->data['properties'][$name][$one] = $v;
							else
								$this->data['properties'][$name][$one] = static::getDefinition()->property($name)->set($v, $this);
					elseif($raw)
						$this->data['properties'][$name][$lang] = $value;
					else
						$this->data['properties'][$name][$lang] = static::getDefinition()->property($name)->set($value, $this);
				}
				elseif($raw)
					$this->data['properties'][$name] = $value;
				else
					$this->data['properties'][$name] = static::getDefinition()->property($name)->set($value, $this);
			}
			#todo use Hookable and hasHook?
			elseif(!$raw && isset(static::getDefinition()->meta['hooks']['set'][$name])) {
				$hook = static::getDefinition()->meta['hooks']['set'][$name];
				$hook($this, $value);
			}
			else
				$this->data[$name] = $value;
		}
				
		return $this;
	}

	public function raw($name, $lang=null) {
		$res = $this->get($name, $lang, true);
		return $res;
	}
	
	public function get($name, $lang=null, $raw=false) {
		if(!$lang)
			$lang = \Config::get('locale');

		#todo go for data[$name] only if orm fetch failed
		$res = static::trigger('get', array($this, $name, $lang), function($chain, $model, $name, $lang) {
			if($model::hasProperty($name)) {
				if($model::property($name)->i18n) {
					if($lang == 'all') {
						$langs = \Config::get('locales');
						$res = array();
						foreach($langs as $lang)
							$res[$lang] = $model->get($name, $lang);
						return $res;
					}
					elseif(isset($model->data['properties'][$name][$lang]))
						return $model->data['properties'][$name][$lang];
				}
				elseif(isset($model->data['properties'][$name])) 
					return $model->data['properties'][$name];
			}
			elseif(isset($model->data[$name]))
				return $model->data[$name];
		});

		return $res;
	}
	
	public function toArrayRaw() {
		$attrs = $this->propertyNames();
		$vars = array();
		
		foreach($attrs as $attr) {
			if(!isset($this->data['properties'][$attr]))
				$vars[$attr] = null;
			else
				$vars[$attr] = $this->data['properties'][$attr];
		}
		
		return $vars;
	}
	
	public function toArray() {
		$vars = array();
		
		foreach($this->properties() as $name=>$property) {
			$vars[$name] = $this->$property;
			if(is_object($vars[$name])) {
				if(method_exists($vars[$name], 'toArray'))
					$vars[$name] = $vars[$name]->toArray();
				elseif(method_exists($vars[$name], '__toString'))
					$vars[$name] = $vars[$name]->__toString();
			}
		}
		
		foreach($this->getDefinition()->relations() as $name=>$relation) {
			if(isset($this->data[$name])) {
				if(is_array($this->data[$name])) {
					$res = array();
					foreach($this->data[$name] as $relation_model)
						$res[] = $relation_model->toArray();
				}
				else
					$res = $this->data[$name]->toArray();

				$vars[$name] = $res;
			}
		}
		
		return $vars;
	}

	public static function arrayToJSON($arr) {
		$res = array();
		foreach($arr as $relation_model)
			$res[] = $relation_model->toArray();
		return json_encode($res);
	}

	public function toJSON() {
		return json_encode($this->toArray());
	}
	
	/* Definition */
	public static function getModelName() {
		return \Coxis\Core\NamespaceUtils::basename(strtolower(get_called_class()));
	}

	public static function hasProperty($name) {
		return static::getDefinition()->hasProperty($name);
	}

	public static function addProperty($property, $params) {
		return static::getDefinition()->addProperty($property, $params);
	}
	
	public static function property($name) {
		return static::getDefinition()->property($name);
	}
	
	public static function properties() {
		return static::getDefinition()->properties();
	}

	public static function propertyNames() {
		return array_keys(static::getDefinition()->properties());
	}
	
	public static function isI18N() {
		return static::getDefinition()->isI18N();
	}

	/* HOOKS */
	protected static function trigger($name, $args=array(), $cb=null) {
		return static::getDefinition()->trigger($name, $args, $cb);
	}

	protected static function triggerChain($chain, $name, $args=array(), $cb=null) {
		return static::getDefinition()->triggerChain($chain, $name, $args, $cb);
	}

	public static function hook() {
		return static::getDefinition()->hook();
	}

	public static function hookOn($hookName, $cb) {
		return static::getDefinition()->hookOn($hookName, $cb);
	}

	public static function hookBefore($hookName, $cb) {
		return static::getDefinition()->hookBefore($hookName, $cb);
	}

	public static function hookAfter($hookName, $cb) {
		return static::getDefinition()->hookAfter($hookName, $cb);
	}
}
