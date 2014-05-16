<?php
namespace Asgard\Validation;

class Report {
	protected $self;
	protected $rules=array();
	protected $attributes=array();

	public function __construct(array $errors=array()) {
		if(isset($errors['self']))
			$this->self = $errors['self'];
		if(isset($errors['rules']))
			$this->rules = $errors['rules'];
		if(isset($errors['attributes'])) {
			foreach($errors['attributes'] as $attribute=>$_errors)
				$this->attributes[$attribute] = new static($_errors);
		}
	}

	public function __toString() {
		return $this->self;
	}

	public function error($rule=null) {
		if($rule === null)
			return $this->self;
		elseif(isset($this->rules[$rule]))
			return $this->rules[$rule];
		elseif($attribute = $this->attribute($rule))
			return $attribute->error();
	}

	public function errors() {
		$errors = $this->rules;
		foreach($this->attributes as $attribute=>$report)
			$errors[$attribute] = $report->error();
		return $errors;
	}

	public function first($attribute=null) {
		if($attribute !== null)
			return $this->attribute($attribute)->first();
		else {
			if($this->rules)
				return array_values($this->rules)[0];
			elseif($this->attributes)
				return array_values($this->attributes)[0]->error();
		}
	}

	public function failed() {
		$failed = array();
		foreach($this->attributes as $attribute=>$report) {
			$attrFailed = $report->failed();
			if($attrFailed)
				$failed[$attribute] = $attrFailed;
			else
				$failed[] = $attribute;
		}
		return $failed;
	}

	public function attribute($attribute, Report $report=null) {
		if(is_string($attribute))
			$attribute = explode('.', $attribute);

		$next = array_shift($attribute);
		if(!isset($this->attributes[$next]))
			$this->attributes[$next] = new static;

		if($report !== null) {
			if(count($attribute) == 0)
				$this->attributes[$next] = $report;
			else
				$this->attributes[$next]->attribute($attribute, $report);
			return $this;
		}
		else {
			if(count($attribute) == 0)
				return $this->attributes[$next];
			else
				return $this->attributes[$next]->attribute($attribute);
		}
	}

	public function attributes() {
		return $this->attributes;
	}

	public function hasError() {
		return $this->rules || $this->attributes;
	}
}