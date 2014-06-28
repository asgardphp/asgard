<?php
namespace Asgard\Validation;
use Symfony\Component\Translation\TranslatorInterface;

 /**
  * @method static attribute($attribute, $rules=null)
  * @method $this attributes(array $attributes)
  * @method $this rules(array $rules, $each=false)
  * @method $this rule($rule, $params=[], $each=false)
  * @method $this isNull($param=true)
  * @method $this required($param=true)
  */
class Validator {
	protected $params = [];
	protected $rules = [];
	protected $attributes = [];
	protected $defaultMessage;
	protected $messages = [];
	protected $required;
	protected $isNull;
	protected $registry;
	protected $input;
	protected $parent;
	protected $name;
	protected $translator;
	protected $formatParameters;

	public function setTranslator(TranslatorInterface $translator) {
		$this->translator = $translator;
		return $this;
	}

	public function getTranslator() {
		if($this->translator)
			return $this->translator;
		elseif($this->parent)
			return $this->parent->getTranslator();
	}

	#to capture the rules calls and required
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

	#same in static
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

	#to define several rules
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

	#to define one rule
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

	#set attribute validator / or get attribute if not rules provided
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

	public function callAttributes(array $attributes) {
		foreach($attributes as $attribute=>$rules)
			$this->attribute($attribute, $rules);
		return $this;
	}

	public function setDefaultMessage($attribute, $message=null) {
		if($message === null)
			$this->defaultMessage = $attribute;
		else
			$this->attribute($attribute)->setDefaultMessage($message);
		return $this;
	}

	public function getDefaultMessage() {
		return $this->defaultMessage;
	}

	public function ruleMessage($rule, $message=null) {
		$this->messages[$rule] = $message;
		return $this;
	}

	public function ruleMessages(array $rules) {
		foreach($rules as $rule=>$message)
			$this->ruleMessage($rule, $message);
		return $this;
	}

	public function attributesMessages(array $messages) {
		foreach($messages as $attribute=>$attrMessages) {
			if($attrMessages)
				$this->attribute($attribute)->ruleMessages($attrMessages);
		}
		return $this;
	}

	public function getRuleMessage($rule) {
		if(isset($this->messages[$rule]))
			return $this->messages[$rule];
		if($this->parent)
			return $this->parent->getRuleMessage($rule);
	}

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

	public function getRegistry() {
		if($this->registry)
			return $this->registry;
		elseif($this->parent)
			return $this->parent->getRegistry();
		else
			return RulesRegistry::getInstance();
	}

	public function setRegistry(RulesRegistry $registry) {
		$this->registry = $registry;
		return $this;
	}

	public function setInput($input) {
		if(!$input instanceof InputBag)
			$input = new InputBag($input);
		return $this->input = $input;
	}

	public function getInput() {
		if($this->input === null)
			return new InputBag(null);
		return $this->input;
	}

	public function setParent(Validator $parent) {
		$this->parent = $parent;
		return $this;
	}

	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	public function assert($input) {
		$report = $this->errors($input);
		if($report->hasError())
			throw new ValidatorException('Validation failed.', $report);
	}

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

	protected function checkIsNull($input) {
		$isNull = $this->isNull;
		return $input === null || $input === '' || ($isNull && $isNull($input));
	}

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

	public function errors($input=null) {
		$errors = $this->_errors($input);
		return new Report($errors);
	}

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

	protected function getMessage() {
		if($message = $this->defaultMessage) {}
		else $message = ':attribute is not valid.';

		$params = [
			'attribute' => $this->getName(),
		];
		return $this->format($message, $params);
	}

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

	public function formatParameters($formatParameters) {
		$this->formatParameters = $formatParameters;
		return $this;
	}

	protected function format($message, array $params) {
		if($fm = $this->formatParameters)
			$fm($params);

		foreach($params as $k=>$v) {
			if(is_string($v) || is_numeric($v))
				$message = str_replace(':'.$k, $v, $message);
		}
		return ucfirst($message);
	}

	public function set($key, $value) {
		$this->params[$key] = $value;
	}

	public function get($key) {
		if(!isset($this->params[$key])) {
			if(!$this->parent)
				throw new \Exception('Parameter '.$key.' does not exist.');
			return $this->parent->get($key);
		}
		return $this->params[$key];
	}
}