<?php
namespace Asgard\Validation;

class InputBag {
	protected $parent;
	protected $input;
	protected $attributes = [];

	public function __construct($input) {
		$this->input = $input;
		if(is_array($input)) {
			foreach($input as $k=>$v)
				$this->attributes[$k] = (new static($v))->setParent($this);
		}
	}

	public function setParent(InputBag $parent) {
		$this->parent = $parent;
		return $this;
	}

	public function parent() {
		if(!$this->parent)
			return $this;
		else
			return $this->parent;
	}

	public function hasAttribute($attribute) {
		return $this->attribute($attribute)->input() !== null;
	}

	public function attribute($attribute) {
		if(!is_array($attribute))
			$attribute = explode('.', $attribute);

		$next = array_shift($attribute);
		if(count($attribute) === 0) {
			if(!isset($this->attributes[$next]))
				return (new static(null))->setParent($this);
			else
				return $this->attributes[$next];
		}

		if($next === '<')
			return $this->parent()->attribute($attribute);
		else {
			if(!isset($this->attributes[$next]))
				return (new static(null))->setParent($this);
			else
				return $this->attributes[$next]->attribute($attribute);
		}
	}

	public function attributes() {
		return $this->attributes;
	}

	public function input($attribute=null) {
		if($attribute)
			return $this->attribute($attribute)->input();
		return $this->input;
	}
}