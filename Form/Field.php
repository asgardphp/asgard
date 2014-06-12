<?php
namespace Asgard\Form;

abstract class Field {
	public $options;
	protected $data_type = 'string';
	protected $dad;
	public $name;
	protected $value;
	protected $default_render = 'text';
	protected $error;

	public function __construct(array $options=[]) {
		$this->options = $options;
		if(isset($options['data_type']))
			$this->data_type = $options['data_type'];
		if(isset($options['default']))
			$this->value = $options['default'];
		if(isset($options['default_render']))
			$this->default_render = $options['default_render'];
	}

	public function getTopForm() {
		return $this->dad->getTopForm();
	}

	public function getValidationRules() {
		$validation = isset($this->options['validation']) ? $this->options['validation']:[];
		if(isset($this->options['choices']))
			$validation['in'] = array_keys($this->options['choices']);

		return $validation;
	}

	public function getValidationMessages() {
		$messages = isset($this->options['messages']) ? $this->options['messages']:[];
		return $messages;
	}

	public function __call($name, array $args) {
		return $this->render($name, isset($args[0]) ? $args[0]:[]);
	}

	public function setDefaultRender($default_render) {
		$this->default_render = $default_render;
	}

	public function label() {
		return ucfirst(str_replace('_', ' ', $this->name));
	}

	public function labelTag() {
		return '<label for="'.$this->getID().'">'.$this->label().'</label>';
	}

	public function def(array $options=[]) {
		if(!$this->default_render)
			throw new \Exception('No default render function for this field');
		return $this->render($this->default_render, $options);
	}

	public function render($render_callback, array $options=[]) {
		return $this->dad->render($render_callback, $this, $options);
	}
	
	public function __toString() {
		return $this->def();
	}
	
	public function getValue() {
		return $this->value;
	}
	
	public function setDad($dad) {
		$this->dad = $dad;
	}
	
	public function getDad() {
		return $this->dad;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function setValue($value) {
		$this->value = $value;
	}
	
	public function getParents() {
		return $this->dad->getParents();
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
	
	public function getName() {
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

	public function setErrors($error) {
		$this->error = $error;
	}

	public function getError() {
		if(is_array($this->error))
			return \Asgard\Common\ArrayUtils::array_get(array_values($this->error), 0);
		else
			return $this->error;
	}
}