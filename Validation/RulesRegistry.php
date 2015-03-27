<?php
namespace Asgard\Validation;

/**
 * Contains the rules for validation.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class RulesRegistry implements RulesRegistryInterface {
	/**
	 * Singleton instance.
	 * @var RulesRegistryInterface
	 */
	protected static $instance;
	/**
	 * Default error messages of rules.
	 * @var array
	 */
	protected $messages = [];
	/**
	 * Registered rules.
	 * @var array
	 */
	protected $rules = [];
	/**
	 * Array of rules namespaces.
	 * @var array
	 */
	protected $namespaces = [
		'\\Asgard\\Validation\\Rules\\'
	];

	/**
	 * Return the singleton.
	 * @return RulesRegistryInterface
	 */
	public static function singleton() {
		if(!static::$instance)
			static::$instance = new static;
		return static::$instance;
	}

	/**
	 * {@inheritDoc}
	 */
	public function message($rule, $message) {
		$rule = strtolower($rule);
		$this->messages[$rule] = $message;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function messages(array $rules) {
		foreach($rules as $rule=>$message)
			$this->message($rule, $message);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage($rule) {
		$rule = strtolower($rule);
		if(isset($this->messages[$rule]))
			return $this->messages[$rule];
	}

	/**
	 * {@inheritDoc}
	 */
	public function register($rule, $object) {
		if($object instanceof \Closure) {
			$reflection = new \ReflectionClass('Asgard\Validation\Rules\Callback');
			$object = $reflection->newInstance($object);
		}
		$this->rules[$rule] = $object;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function registerNamespace($namespace) {
		$namespace = '\\'.trim($namespace, '\\').'\\';
		if(!in_array($namespace, $this->namespaces))
			array_unshift($this->namespaces, $namespace);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRule($rule, array $params=[]) {
		if($rule === 'required' || $rule === 'isNull')
			return;

		if(isset($this->rules[$rule]))
			$rule = $this->rules[$rule];
		else {
			foreach($this->namespaces as $namespace) {
				$class = $namespace.ucfirst($rule);
				if(class_exists($class) && is_subclass_of($class, 'Asgard\Validation\Rule')) {
					$rule = $class;
					break;
				}
			}
		}

		if(is_string($rule) && class_exists($rule)) {
			$reflection = new \ReflectionClass($rule);
			$rule = $reflection->newInstanceArgs($params);
			return $rule;
		}
		elseif(is_object($rule))
			return $rule;

		throw new \Exception('Rule "'.$rule.'" does not exist.');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRuleName($rule) {
		foreach($this->rules as $name=>$class) {
			if($class === get_class($rule))
				return $name;
		}
		$explode = explode('\\', get_class($rule));
		return $explode[count($explode)-1];
	}
}