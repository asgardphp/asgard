<?php
namespace Asgard\Validation;

/**
 * Contains and manipulates inputs for validation.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class InputBag {
	/**
	 * Parent input
	 * @var InputBag
	 */
	protected $parent;
	/**
	 * Raw input
	 * @var mixed
	 */
	protected $input;
	/**
	 * Children inputs
	 * @var array<InputBag>
	 */
	protected $attributes = [];

	/**
	 * Constructor.
	 * @param mixed $input
	 */
	public function __construct($input) {
		$this->input = $input;
		if(is_array($input)) {
			foreach($input as $k=>$v)
				$this->attributes[$k] = (new static($v))->setParent($this);
		}
	}

	/**
	 * Set the parent input bag.
	 * @param InputBag $parent
	 */
	public function setParent(InputBag $parent) {
		$this->parent = $parent;
		return $this;
	}

	/**
	 * Return the parent input bag.
	 * @return InputBag
	 */
	public function parent() {
		if(!$this->parent)
			return $this;
		else
			return $this->parent;
	}

	/**
	 * Check if the input has an attribute.
	 * @param  string  $attribute name of attribute
	 * @return boolean            true if attribute exists, or false
	 */
	public function hasAttribute($attribute) {
		return $this->attribute($attribute)->input() !== null;
	}

	/**
	 * Return an children input bag.
	 * @param  array|string $attribute name of attribute
	 * @return InputBag
	 */
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

	/**
	 * Return all the children input bags.
	 * @return array<InputBag>
	 */
	public function attributes() {
		return $this->attributes;
	}

	/**
	 * Return the raw input of an attribute.
	 * @param  string $attribute name of attribute
	 * @return mixed
	 */
	public function input($attribute=null) {
		if($attribute)
			return $this->attribute($attribute)->input();
		return $this->input;
	}
}