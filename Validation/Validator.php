<?php
namespace Asgard\Validation;
use Symfony\Component\Translation\TranslatorInterface;

 /**
  * Validator.
  * @method $this isNull($param=true)
  * @method $this required($param=true)
 * @author Michel Hognerud <michel@hognerud.com>
  */
class Validator implements ValidatorInterface {
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
	 * @var RulesRegistryInterface
	 */
	protected $registry;
	/**
	 * Input.
	 * @var mixed
	 */
	protected $input;
	/**
	 * Parent validator.
	 * @var ValidatorInterface
	 */
	protected $parent;
	/**
	 * Validator name.
	 * @var string
	 */
	protected $name;
	/**
	 * Translator.
	 * @var \Symfony\Component\Translation\TranslatorInterface
	 */
	protected $translator;
	/**
	 * Callback to format parameters.
	 * @var callback
	 */
	protected $formatParameters;

	/**
	 * Groups.
	 * @var array|null
	 */
	protected $groups;

	/**
	 * Constructor.
	 * @param RulesRegistryInterface $registry
	 */
	public function __construct(RulesRegistryInterface $registry=null) {
		$this->registry = $registry;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setTranslator(TranslatorInterface $translator=null) {
		$this->translator = $translator;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTranslator() {
		if($this->translator)
			return $this->translator;
		elseif($this->parent)
			return $this->parent->getTranslator();
	}

	/**
	 * {@inheritDoc}
	 */
	public function __call($name, array $args) {
		return call_user_func_array([$this, 'rule'], [$name, $args]);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function __callStatic($name, array $args) {
		$v = new static;
		return call_user_func_array([$v, 'rule'], [$name, $args]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rule($rule, $params=[], $each=false) {
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
					$this->rule($r, $params);
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
	 * {@inheritDoc}
	 */
	public function rules(array $rules, $each=false) {
		if(count($rules) === 2 && isset($rules['each']) && isset($rules['self'])) {
			$this->rules($rules['each'], true);
			$this->rules($rules['self'], false);
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
	 * {@inheritDoc}
	 */
	public function hasAttribute($attribute) {
		if(!is_array($attribute))
			$attribute = explode('.', $attribute);

		$next = array_shift($attribute);
		if(count($attribute) > 0) {
			if(!isset($this->attributes[$next]))
				return false;
			else
				return $this->attributes[$next]->attribute($attribute);
		}
		else {
			if(!isset($this->attributes[$next]))
				return false;
			else
				return true;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function attribute($attribute, $rules=null) {
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
			if(!isset($this->attributes[$next])) {
				$this->attributes[$next] = new static;
				$this->attributes[$next]->setParent($this);
				$this->attributes[$next]->setName($next);
			}
			if($rules !== null) {
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
	 * {@inheritDoc}
	 */
	public function attributes(array $attributes) {
		foreach($attributes as $attribute=>$rules)
			$this->attribute($attribute, $rules);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDefaultMessage($attribute, $message=null) {
		if($message === null)
			$this->defaultMessage = $attribute;
		else
			$this->attribute($attribute)->setDefaultMessage($message);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefaultMessage() {
		return $this->defaultMessage;
	}

	/**
	 * {@inheritDoc}
	 */
	public function ruleMessage($rule, $message=null) {
		$this->messages[$rule] = $message;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function ruleMessages(array $rules) {
		foreach($rules as $rule=>$message)
			$this->ruleMessage($rule, $message);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function attributesMessages(array $messages) {
		foreach($messages as $attribute=>$attrMessages) {
			if($attrMessages)
				$this->attribute($attribute)->ruleMessages($attrMessages);
		}
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRuleMessage($rule) {
		if(isset($this->messages[$rule]))
			return $this->messages[$rule];
		if($this->parent)
			return $this->parent->getRuleMessage($rule);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRule($rule, array $params) {
		if(isset($params['groups'])) {
			$groups = $params['groups'];
			unset($params['groups']);
		}
		else
			$groups = null;

		#callback
		if($rule instanceof \Closure) {
			$reflection = new \ReflectionClass('Asgard\Validation\Rules\Callback');
			$rule = $reflection->newInstance($rule);
		}
		#string
		elseif(is_string($rule))
			$rule = $this->getRegistry()->getRule($rule, $params);

		$rule->setGroups($groups);

		return $rule;
	}

	/**
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 */
	public function setRegistry(RulesRegistryInterface $registry) {
		$this->registry = $registry;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setInput($input) {
		if(!$input instanceof InputBag)
			$input = new InputBag($input);
		return $this->input = $input;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getInput() {
		if($this->input === null)
			return new InputBag(null);
		return $this->input;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setParent(ValidatorInterface $parent) {
		$this->parent = $parent;
		return $this;
	}

	public function getParent() {
		return $this->parent;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function assert($input) {
		$report = $this->errors($input);
		if($report->hasError())
			throw new ValidatorException('Validation failed.', $report);
	}

	/**
	 * {@inheritDoc}
	 */
	public function validRule($rule, array $groups=[]) {
		if($rule instanceof static) {
			$rule->setInput($this->input);
			if($rule->valid(null, $groups) === false)
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
		if($isNull)
			return $isNull($input);
		else
			return $input === null || $input === '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function valid($input=null, $groups=[]) {
		if($groups === null)
			return true;
		if(!$groups)
			$groups = ['default'];

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
			if(!$rule->belongsToGroups($groups, $this))
				continue;
			if($this->validRule($rule, $groups) === false)
				return false;
		}
		foreach($this->attributes as $name=>$validator) {
			if($validator->valid($input->attribute($name), $groups) === false)
				return false;
		}
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function errors($input=null, $groups=[]) {
		if($groups === null)
			return new Report([]);
		if(!$groups)
			$groups = ['default'];

		$errors = $this->_errors($input, $groups);
		return new Report($errors);
	}

	/**
	 * {@inheritDoc}
	 */
	public function _errors($input=null, array $groups=[]) {
		if(!$groups)
			$groups = ['default'];

		if($input === null)
			$input = $this->getInput();
		else
			$input = $this->setInput($input);

		$errors = ['self'=>null, 'rules'=>[], 'attributes'=>[]];
		if($this->required instanceof \Closure)
			$required = call_user_func_array($this->required, [$input->input(), $input, $this]);
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
				if(!$rule->belongsToGroups($groups, $this))
					continue;
				if($this->validRule($rule, $groups) === false) {
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
						$errors = $this->mergeErrors($errors, $rule->_errors($input, $groups));
				}
			}
			foreach($this->attributes as $attribute=>$validator) {
				$attrErrors = $validator->_errors($input->attribute($attribute), $groups);
				if($attrErrors['self'] || $attrErrors['attributes']) {
					if(isset($errors['attributes'][$attribute]))
						$errors['attributes'][$attribute] = $this->mergeErrors($errors['attributes'][$attribute], $attrErrors);
					else
						$errors['attributes'][$attribute] = $attrErrors;
				}
			}
		}

		$attrErrors = array_filter(\Asgard\Common\ArrayUtils::flatten($errors['attributes']));
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
	 * {@inheritDoc}
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
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 */
	public function set($key, $value) {
		$this->params[$key] = $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get($key) {
		if(!isset($this->params[$key])) {
			if(!$this->parent)
				return;
			return $this->parent->get($key);
		}
		return $this->params[$key];
	}

	/**
	 * {@inheritDoc}
	 */
	public function setGroups($groups=null) {
		if(!is_array($groups) && $groups !== null)
			$groups = [$groups];
		$this->groups = $groups;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getGroups() {
		return $this->groups;
	}

	/**
	 * {@inheritDoc}
	 */
	public function belongsToGroups(array $groups) {
		if($this->groups === null) {
			if(!$this->parent)
				return in_array('default', $groups);
			else
				return $this->parent->belongsToGroups($groups);
		}
		else {
			foreach($groups as $group) {
				if(in_array($group, $this->groups))
					return true;
			}
			return false;
		}
	}
}