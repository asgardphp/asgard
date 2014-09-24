<?php
namespace Asgard\Validation;
use Symfony\Component\Translation\TranslatorInterface;

 /**
  * Validator.
  * @method static attribute($attribute, $rules=null)
  * @method $this attributes(array $attributes)
  * @method $this rules(array $rules, $each=false)
  * @method $this rule($rule, $params=[], $each=false)
  * @method $this isNull($param=true)
  * @method $this required($param=true)
  */
class Validator {
	/**
	 * Validator parameters.
	 * @var array
	 */
	protected $params = [];
	/**
	 * Validator rules.
	 * @var array
	 */
	protected $rules = [];
	/**
	 * Validator attributes.
	 * @var array
	 */
	protected $attributes = [];
	/**
	 * Default error message.
	 * @var string
	 */
	protected $defaultMessage;
	/**
	 * Rules default messages.
	 * @var array
	 */
	protected $messages = [];
	/**
	 * "Required" rule.
	 * @var boolean
	 */
	protected $required;
	/**
	 * "isNull" callback, to determine empty inputs.
	 * @var callback
	 */
	protected $isNull;
	/**
	 * Rules registry.
	 * @var RulesRegistry
	 */
	protected $registry;
	/**
	 * Input.
	 * @var mixed
	 */
	protected $input;
	/**
	 * Parent validator.
	 * @var Validator
	 */
	protected $parent;
	/**
	 * Validator name.
	 * @var string
	 */
	protected $name;
	/**
	 * Translator.
	 * @var Symfony\Component\Translation\TranslatorInterface
	 */
	protected $translator;
	/**
	 * Callback to format parameters.
	 * @var callback
	 */
	protected $formatParameters;

	/**
	 * Set the translator.
	 * @param \Symfony\Component\Translation\TranslatorInterface $translator
	 */
	public function setTranslator(TranslatorInterface $translator) {
		$this->translator = $translator;
		return $this;
	}

	/**
	 * Get the translator.
	 * @return \Symfony\Component\Translation\TranslatorInterface
	 */
	public function getTranslator() {
		if($this->translator)
			return $this->translator;
		elseif($this->parent)
			return $this->parent->getTranslator();
	}

	/**
	 * Capture the calls to rules. Magic __call method.
	 * @param  string $name
	 * @param  array  $args
	 * @return mixed
	 */
	public function __call($name, array $args) {
		if($name == 'attribute')
			return call_user_func_array([$this, 'callAttribute'], $args);
		if($name == 'attributes')
			return call_user_func_array([$this, 'callAttributes'], $args);
		if($name == 'rules')
			return call_user_func_array([$this, 'callRules'], $args);
		if($name == 'rule')
			return call_user_func_array([$this, 'callRule'], $args);
		return call_user_func_array([$this, 'callRule'], [$name, $args]);
	}

	/**
	 * Capture the static calls to rules. MAgic __callStatic method.
	 * @param  [type] $name
	 * @param  array  $args
	 * @return mixed
	 */
	public static function __callStatic($name, array $args) {
		$v = new static;
		if($name == 'attribute')
			return call_user_func_array([$v, 'callAttribute'], $args);
		if($name == 'attributes')
			return call_user_func_array([$v, 'callAttributes'], $args);
		if($name == 'rules')
			return call_user_func_array([$v, 'callRules'], $args);
		if($name == 'rule')
			return call_user_func_array([$v, 'callRule'], $args);
		return call_user_func_array([$v, 'callRule'], [$name, $args]);
	}

	/**
	 * Set a rule.
	 * @param  string  $rule   rule name
	 * @param  array   $params rule parameter
	 * @param  boolean $each   to validate the rule against each input of an array.
	 * @return Validator       $this
	 */
	protected function callRule($rule, $params=[], $each=false) {
		if(!is_array($params))
			$params = [$params];
		if($rule === 'required')
			$this->required = isset($params[0]) ? $params[0]:true;
		elseif($rule === 'isNull')
			$this->isNull = $params[0];
		else {
			if(is_string($rule) && preg_match('/\||:/', $rule)) {
				foreach(explode('|', $rule) as $r) {
					list($r, $params) = explode(':', $r);
					$params = explode(',', $params);
					$this->callRule($r, $params);
				}
				return $this;
			}
			$rule = $this->getRule($rule, $params);
			if($rule instanceof static)
				$rule->setParent($this);
			if($each)
				$rule->handleEach(true);
			$this->rules[] = $rule;
		}
		return $this;
	}

	/**
	 * Set multiple rules.
	 * @param  array   $rules [description]
	 * @param  boolean $each  [description]
	 * @return Validator       $this
	 */
	protected function callRules(array $rules, $each=false) {
		if(count($rules) === 2 && isset($rules['each']) && isset($rules['self'])) {
			$this->callRules($rules['each'], true);
			$this->callRules($rules['self'], false);
			return $this;
		}

		foreach($rules as $key=>$value) {
			if(is_numeric($key))
				$this->rule($value, $each);
			else
				$this->rule($key, $value, $each);
		}
		return $this;
	}

	/**
	 * Set an attribute validator or only return the attribute validator if no rules given.
	 * @param  string $attribute attribute name
	 * @param  array  $rules     attribute rules
	 * @return Validator         $this or the attribute validator.
	 */
	protected function callAttribute($attribute, $rules=null) {
		if(!is_array($attribute))
			$attribute = explode('.', $attribute);

		$next = array_shift($attribute);
		if(count($attribute) > 0) {
			if(!isset($this->attributes[$next])) {
				$this->attributes[$next] = new static;
				$this->attributes[$next]->setParent($this);
				$this->attributes[$next]->setName($next);
			}
			$res = $this->attributes[$next]->attribute($attribute, $rules);
			if($rules === null)
				return $res;
			return $this;
		}
		else {
			if(!isset($this->attributes[$next]))
				$this->attributes[$next] = new static;
			if($rules !== null) {
				$this->attributes[$next]->setParent($this);
				$this->attributes[$next]->setName($next);
				if(is_array($rules))
					$this->attributes[$next]->rules($rules);
				else
					$this->attributes[$next]->rule($rules);
				return $this;
			}
			else
				return $this->attributes[$next];
		}
	}

	/**
	 * Set attributes rules.
	 * @param  array  $attributes
	 * @return Validator       $this
	 */
	public function callAttributes(array $attributes) {
		foreach($attributes as $attribute=>$rules)
			$this->attribute($attribute, $rules);
		return $this;
	}

	/**
	 * Set the default message for an attribute.
	 * @param string $attribute attribute name
	 * @param string $message
	 * @return Validator       $this
	 */
	public function setDefaultMessage($attribute, $message=null) {
		if($message === null)
			$this->defaultMessage = $attribute;
		else
			$this->attribute($attribute)->setDefaultMessage($message);
		return $this;
	}

	/**
	 * Get the default error message.
	 * @return string
	 */
	public function getDefaultMessage() {
		return $this->defaultMessage;
	}

	/**
	 * Set the default message for a rule.
	 * @param  string $rule    rule name
	 * @param  string $message
	 * @return Validator       $this
	 */
	public function ruleMessage($rule, $message=null) {
		$this->messages[$rule] = $message;
		return $this;
	}

	/**
	 * Set multiple rules messages.
	 * @param  array  $rules
	 * @return Validator       $this
	 */
	public function ruleMessages(array $rules) {
		foreach($rules as $rule=>$message)
			$this->ruleMessage($rule, $message);
		return $this;
	}

	/**
	 * Set multiple attributes messages.
	 * @param  array  $messages
	 * @return Validator       $this
	 */
	public function attributesMessages(array $messages) {
		foreach($messages as $attribute=>$attrMessages) {
			if($attrMessages)
				$this->attribute($attribute)->ruleMessages($attrMessages);
		}
		return $this;
	}

	/**
	 * Set the default message for a rule.
	 * @param  string $rule    rule name
	 * @return string
	 */
	public function getRuleMessage($rule) {
		if(isset($this->messages[$rule]))
			return $this->messages[$rule];
		if($this->parent)
			return $this->parent->getRuleMessage($rule);
	}

	/**
	 * Get an instance of a rule.
	 * @param  string $rule    rule name
	 * @param  array  $params [description]
	 * @return Rule
	 */
	public function getRule($rule, array $params) {
		#validator
		if($rule instanceof static)
			return $rule;
		#rule
		elseif($rule instanceof Rule)
			return $rule;
		#callback
		elseif($rule instanceof \Closure) {
			$reflection = new \ReflectionClass('Asgard\Validation\Rules\Callback');
			return $reflection->newInstance($rule);
		}
		#string
		elseif(is_string($rule))
			return $this->getRegistry()->getRule($rule, $params);
	}

	/**
	 * Get the RulesRegistry instance.
	 * @return RulesRegistry
	 */
	public function getRegistry() {
		if($this->registry)
			return $this->registry;
		elseif($this->parent)
			return $this->parent->getRegistry();
		else
			return RulesRegistry::singleton();
	}

	/**
	 * Set the RulesRegistry instance.
	 * @param RulesRegistry $registry
	 * @return Validator       $this
	 */
	public function setRegistry(RulesRegistry $registry) {
		$this->registry = $registry;
		return $this;
	}

	/**
	 * Set the input for validation.
	 * @param mixed $input
	 */
	public function setInput($input) {
		if(!$input instanceof InputBag)
			$input = new InputBag($input);
		return $this->input = $input;
	}

	/**
	 * Get the input.
	 * @return mixed
	 */
	public function getInput() {
		if($this->input === null)
			return new InputBag(null);
		return $this->input;
	}

	/**
	 * Set the parent validator.
	 * @param Validator $parent
	 * @return Validator       $this
	 */
	public function setParent(Validator $parent) {
		$this->parent = $parent;
		return $this;
	}

	/**
	 * Set the validator name.
	 * @param string $name
	 * @return Validator       $this
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * Raise an exception if there is an error for the given input.
	 * @param  mixed $input
	 * @throws ValidatorException If there is an error for the given input.
	 * @return null
	 */
	public function assert($input) {
		$report = $this->errors($input);
		if($report->hasError())
			throw new ValidatorException('Validation failed.', $report);
	}

	/**
	 * Check if a rule validates the input.
	 * @param  string $rule    rule name
	 * @return boolean         true if the input is valid, otherwise false.
	 */
	public function validRule($rule) {
		if($rule instanceof static) {
			$rule->setInput($this->input);
			if($rule->valid() === false)
				return false;
		}
		elseif($rule instanceof Rule) {
			$input = $this->getInput()->input();
			if($rule->isHandlingEach() && is_array($input)) {
				foreach($input as $k=>$v) {
					if($rule->validate($v, $this->getInput(), $this) === false)
						return false;
				}
				return true;
			}
			if($rule->validate($input, $this->getInput()->parent(), $this) === false)
				return false;
		}
		return true;
	}

	/**
	 * Check if the input is null.
	 * @param  mixed $input
	 * @return boolean        true if the input is considered null, false otherwise.
	 */
	protected function checkIsNull($input) {
		$isNull = $this->isNull;
		return $input === null || $input === '' || ($isNull && $isNull($input));
	}

	/**
	 * Check the input is valid.
	 * @param  mixed $input
	 * @return boolean        true is the input is valid, false otherwise.
	 */
	public function valid($input=null) {
		if($input === null)
			$input = $this->getInput();
		else
			$input = $this->setInput($input);

		#if input is null, return false if required, or true
		if($this->checkIsNull($input->input())) {
			if(($required = $this->required) instanceof \Closure)
				return !$required(null, $input->parent(), $this);
			else
				return !$required;
		}
		$i=0; #rules may be added on the fly
		while(isset($this->rules[$i])) {
			$rule = $this->rules[$i++];
			if($this->validRule($rule) === false)
				return false;
		}
		foreach($this->attributes as $name=>$validator) {
			if($validator->valid($input->attribute($name)) === false)
				return false;
		}
		return true;
	}

	/**
	 * Return the errors report.
	 * @param  mixed $input
	 * @return Report
	 */
	public function errors($input=null) {
		$errors = $this->_errors($input);
		return new Report($errors);
	}

	/**
	 * Return the raw errors.
	 * @param  mixed $input
	 * @return array
	 */
	public function _errors($input=null) {
		if($input === null)
			$input = $this->getInput();
		else
			$input = $this->setInput($input);

		$errors = ['self'=>null, 'rules'=>[], 'attributes'=>[]];
		if($this->required instanceof \Closure)
			$required = $this->required();
		else
			$required = $this->required;

		if($this->checkIsNull($input->input())) {
			if($required)
				$errors['rules']['required'] = $this->buildRuleMessage('required', null, ':attribute is required.', $input->input());
		}
		else {
			$i=0; #rules may be added on the fly
			while(isset($this->rules[$i])) {
				$rule = $this->rules[$i++];
				if($this->validRule($rule) === false) {
					if($rule instanceof Rule) {
						$origName = $name = strtolower($this->getRegistry()->getRuleName($rule));
						if(isset($errors['rules'][$name])) {
							$j=1;
							while(isset($errors['rules'][$name.'-'.$j])) { $j += 1; }
							$name = $name.'-'.$j;
						}
						$errors['rules'][strtolower($name)] = $this->buildRuleMessage($origName, $rule, null, $input->input());
					}
					elseif($rule instanceof static)
						$errors = $this->mergeErrors($errors, $rule->_errors($input));
				}
			}
			foreach($this->attributes as $attribute=>$validator) {
				$attrErrors = $validator->_errors($input->attribute($attribute));
				if($attrErrors['self'] || $attrErrors['attributes']) {
					if(isset($errors['attributes'][$attribute]))
						$errors['attributes'][$attribute] = $this->mergeErrors($errors['attributes'][$attribute], $attrErrors);
					else
						$errors['attributes'][$attribute] = $attrErrors;
				}
			}
		}

		$attrErrors = array_filter(\Asgard\Common\ArrayUtils::flateArray($errors['attributes']));
		if(!$errors['self'] && ($errors['rules'] || $attrErrors)) {
			$allErrors = array_merge($errors['rules'], $attrErrors);
			if(count($allErrors) === 1)
				$errors['self'] = array_values($allErrors)[0];
			else
				$errors['self'] = $this->getMessage();
		}

		return $errors;
	}

	/**
	 * Merge two array of errors.
	 * @param  array  $errors1
	 * @param  array  $errors2
	 * @return array
	 */
	protected function mergeErrors(array $errors1, array $errors2) {
		foreach($errors2['rules'] as $name=>$rule) {
			if(isset($errors1['rules'][$name])) {
				$i=1;
				while(isset($errors1['rules'][$name.'-'.$i])) { $i += 1; }
				$name = $name.'-'.$i;
			}
			$errors1['rules'][$name] = $rule;
		}
		foreach($errors2['attributes'] as $attribute=>$errors) {
			if(isset($errors1['attributes'][$attribute]))
				$errors1['attributes'][$attribute] = $this->mergeErrors($errors1['attributes'][$attribute], $errors2['attributes'][$attribute]);
			else
				$errors1['attributes'] = $errors2['attributes'];
		}
		return $errors1;
	}

	/**
	 * Get the validator name.
	 * @return string
	 */
	public function getName() {
		if($this->name)
			return $this->name;
		elseif($this->parent)
			return $this->parent->getName();
		elseif(!$this->input)
			return;
		else {
			$input = $this->input->input();
			if(is_object($input)) {
				if($this->getTranslator())
					return $this->getTranslator()->trans('Object');
				return 'Object';
			}
			elseif(is_array($input)) {
				if($this->getTranslator())
					return $this->getTranslator()->trans('Array');
				return 'Array';
			}
			else
				return '"'.$input.'"';
		}
	}

	/**
	 * Get the default error message.
	 * @return string
	 */
	protected function getMessage() {
		if($message = $this->defaultMessage) {}
		else $message = ':attribute is not valid.';

		$params = [
			'attribute' => $this->getName(),
		];
		return $this->format($message, $params);
	}

	/**
	 * Build the error message of a rule.
	 * @param  string $ruleName
	 * @param  Rule $rule
	 * @param  string $default  default message
	 * @param  mixed $input
	 * @return string
	 */
	protected function buildRuleMessage($ruleName, $rule=null, $default=null, $input=null) {
		if($message = $this->getRuleMessage($ruleName)) {}
		elseif($message = $this->getRegistry()->getMessage($ruleName)) {}
		elseif($rule !== null && $message = $rule->getMessage()) {}
		elseif($default !== null && $message=$default) {}
		elseif($message = $this->getDefaultMessage()) {}
		else $message = ':attribute is not valid.';

		$params = [
			'attribute' => $this->getName(),
		];
		if(is_string($input) || is_numeric($input))
			$params['input'] = $input;
		if($rule instanceof Rule) {
			$params = array_merge($params, get_object_vars($rule));
			$rule->formatParameters($params);
		}

		if($this->getTranslator())
			$message = $this->getTranslator()->trans($message);

		return $this->format($message, $params);
	}

	/**
	 * Format parameters before passing them to the message.
	 * @param  array        $formatParameters
	 * @return Validator    $this
	 */
	public function formatParameters($formatParameters) {
		$this->formatParameters = $formatParameters;
		return $this;
	}

	/**
	 * Format an error message.
	 * @param  string $message
	 * @param  array  $params
	 * @return string
	 */
	protected function format($message, array $params) {
		if($fm = $this->formatParameters)
			$fm($params);

		foreach($params as $k=>$v) {
			if(is_string($v) || is_numeric($v))
				$message = str_replace(':'.$k, $v, $message);
		}
		return ucfirst($message);
	}

	/**
	 * Set a parameter.
	 * @param string $key
	 * @param mixed $value
	 */
	public function set($key, $value) {
		$this->params[$key] = $value;
	}

	/**
	 * Get a parameter.
	 * @param  string $key
	 * @return mixed
	 */
	public function get($key) {
		if(!isset($this->params[$key])) {
			if(!$this->parent)
				throw new \Exception('Parameter '.$key.' does not exist.');
			return $this->parent->get($key);
		}
		return $this->params[$key];
	}
}