<?php
namespace Asgard\Form;

abstract class Field {
	public    $options   = [];
	protected $data_type = 'string';
	protected $parent;
	public    $name;
	protected $value;
	protected $widget    = 'text';
	protected $errors    = [];

	public function __construct(array $options=[]) {
		$this->setoptions($options);
	}

	public function setOptions($options) {
		$this->options = array_merge_recursive($this->options, $options);
		if(isset($this->options['data_type']))
			$this->data_type = $options['data_type'];
		if(isset($this->options['default']))
			$this->value = $this->options['default'];
		if(isset($this->options['widget']))
			$this->widget = $this->options['widget'];

		return $this;
	}

	public function getTopForm() {
		return $this->parent->getTopForm();
	}

	public function getValidationRules() {
		$validation = isset($this->options['validation']) ? $this->options['validation']:[];
		if(isset($this->options['choices']))
			$validation['in'] = [array_keys($this->options['choices'])];

		return $validation;
	}

	public function getValidationMessages() {
		$messages = isset($this->options['messages']) ? $this->options['messages']:[];
		return $messages;
	}

	public function __call($name, array $args) {
		return $this->render($name, isset($args[0]) ? $args[0]:[]);
	}

	public function setDefaultWidget($widget) {
		$this->widget = $widget;
	}

	public function label() {
		return ucfirst(str_replace('_', ' ', $this->name));
	}

	public function labelTag() {
		return '<label for="'.$this->getID().'">'.$this->label().'</label>';
	}

	public function def(array $options=[]) {
		if(!$this->widget)
			throw new \Exception('No default render function for this field');
		return $this->render($this->widget, $options);
	}

	public function render($render_callback, array $options=[]) {
		return $this->parent->render($render_callback, $this, $options);
	}
	
	public function __toString() {
		return $this->def();
	}
	
	public function value() {
		return $this->value;
	}
	
	public function setParent($parent) {
		$this->parent = $parent;
	}
	
	public function getParent() {
		return $this->parent;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function setValue($value) {
		$this->value = $value;
	}
	
	public function getParents() {
		return $this->parent->getParents();
	}
	
	public function getID() {
		$parents = $this->getParents();
		
		if(count($parents) > 0) {
			$id = $parents[0].'-';
			for($i=1; $i<count($parents); $i++)
				$id .= $parents[$i].'-';
			$id .= $this->name;
			return $id;
		}
		else
			return $this->name;
	}
	
	public function name() {
		$parents = $this->getParents();
	
		if(count($parents) > 0) {
			$id = $parents[0];
			for($i=1; $i<count($parents); $i++)
				$id .= '['.$parents[$i].']';
			$id .= '['.$this->name.']';
			return $id;
		}
		else
			return $this->name;
	}

	public function setErrors($errors) {
		$this->errors = $errors;
	}

	public function error() {
		if(isset(array_values($this->errors)[0]))
			return array_values($this->errors)[0];
	}

	public function errors() {
		return $this->errors;
	}
}