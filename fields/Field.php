<?php
namespace Coxis\Form\Fields;

abstract class Field {
	public $options;
	protected $data_type = 'string';
	protected $dad;
	public $name;
	protected $value;
	protected $default_render = 'text';
	protected $error;
	public $form;

	function __construct($options=array()) {
		$this->options = $options;
		if(isset($options['data_type']))
			$this->data_type = $options['data_type'];
		if(isset($options['default']))
			$this->value = $options['default'];
		if(isset($options['form']))
			$this->form = $options['form'];
		if(isset($options['default_render']))
			$this->form = $options['default_render'];
	}

	public function __call($name, $args) {
		return $this->render($name, isset($args[0]) ? $args[0]:array());
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

	public function def($options=array()) {
		if(!$this->default_render)
			throw new \Exception('No default render function for this field');
		return $this->render($this->default_render, $options);
	}

	public function render($render_callback, $options=array()) {
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
		
		if(sizeof($parents) > 0) {
			$id = $parents[0].'-';
			for($i=1; $i<sizeof($parents); $i++)
				$id .= $parents[$i].'-';
			$id .= $this->name;
			return $id;
		}
		else
			return $this->name;
	}
	
	public function getName() {
		$parents = $this->getParents();
		
		if(sizeof($parents) > 0) {
			$id = $parents[0];
			for($i=1; $i<sizeof($parents); $i++)
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
			return \Coxis\Utils\Tools::get(array_values($this->error), 0);
		else
			return $this->error;

		// return $this->dad->getError($this->name);

		// if(isset($this->dad->errors[$this->name])) {
		// 	if(is_array($this->dad->errors[$this->name]))
		// 		return $this->dad->errors[$this->name][0];
		// 	else
		// 		return $this->dad->errors[$this->name];
		// }

				// $res = '';
				// foreach($this->dad->errors[$this->name] as $error)
				// 	$res .= $error."<br/>\n";
				// return $res;
	}
}