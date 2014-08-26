<?php
namespace Asgard\Validation;

/**
 * Contains the rules for validation.
 */
class RulesRegistry {
	/**
	 * Singleton instance.
	 * @var RulesRegistry
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
	 * @return RulesRegistry
	 */
	public static function singleton() {
		if(!static::$instance)
			static::$instance = new static;
		return static::$instance;
	}

	/**
	 * Set the default message of a rule.
	 * @param  string $rule    rule name
	 * @param  string $message
	 * @return RulesRegistry          $this
	 */
	public function message($rule, $message) {
		$rule = strtolower($rule);
		$this->messages[$rule] = $message;
		return $this;
	}

	/**
	 * Set an array of rules messages.
	 * @param  array  $rules
	 * @return RulesRegistry          $this
	 */
	public function messages(array $rules) {
		foreach($rules as $rule=>$message)
			$this->message($rule, $message);
		return $this;
	}

	/**
	 * Get the default message of a rule.
	 * @param  string $rule rule name
	 * @return string
	 */
	public function getMessage($rule) {
		$rule = strtolower($rule);
		if(isset($this->messages[$rule]))
			return $this->messages[$rule];
	}

	/**
	 * Register a rule.
	 * @param  string $rule   rule name
	 * @param  \Closure|Rule $object
	 * @return RulesRegistry          $this
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
	 * Register a namespace.
	 * @param  string $namespace
	 * @return RulesRegistry          $this
	 */
	public function registerNamespace($namespace) {
		$namespace = '\\'.trim($namespace, '\\').'\\';
		if(!in_array($namespace, $this->namespaces))
			array_unshift($this->namespaces, $namespace);
		return $this;
	}

	/**
	 * Get a rule instance.
	 * @param  string $rule   rule name
	 * @param  array $params rule parameters
	 * @return Rule
	 */
	public function getRule($rule, $params=[]) {
		if($rule === 'required' || $rule === 'isNull')
			return;

		if(isset($this->rules[$rule])) {
			$rule = $this->rules[$rule];
		}
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
	 * Get the name of a rule.
	 * @param  Rule $rule rule object
	 * @return string       rule name
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