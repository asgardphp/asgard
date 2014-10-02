<?php
namespace Asgard\Form;

/**
 * Field.
 */
abstract class Field {
	/**
	 * Field options
	 * @var array
	 */
	public $options = [];
	/**
	 * Data type.
	 * @var string
	 */
	protected $data_type = 'string';
	/**
	 * Parent.
	 * @var GroupInterface
	 */
	protected $parent;
	/**
	 * Name.
	 * @var string
	 */
	public    $name;
	/**
	 * Value.
	 * @var mixed
	 */
	protected $value;
	/**
	 * Widget.
	 * @var string|callable
	 */
	protected $widget    = 'text';
	/**
	 * Errors.
	 * @var array
	 */
	protected $errors    = [];

	/**
	 * Constructor.
	 * @param array $options
	 */
	public function __construct(array $options=[]) {
		$this->setoptions($options);
	}

	/**
	 * Set field options.
	 * @param array $options
	 */
	public function setOptions(array $options) {
		$this->options = array_merge_recursive($this->options, $options);
		if(isset($this->options['data_type']))
			$this->data_type = $options['data_type'];
		if(isset($this->options['default']))
			$this->value = $this->options['default'];
		if(isset($this->options['widget']))
			$this->widget = $this->options['widget'];

		return $this;
	}

	/**
	 * Get the top parent form.
	 * @return FormInterface
	 */
	public function getTopForm() {
		return $this->parent->getTopForm();
	}

	/**
	 * Get field validation rules.
	 * @return array
	 */
	public function getValidationRules() {
		$validation = isset($this->options['validation']) ? $this->options['validation']:[];
		if(isset($this->options['choices']))
			$validation['in'] = [array_keys($this->options['choices'])];

		return $validation;
	}

	/**
	 * Get field validation messages.
	 * @return array
	 */
	public function getValidationMessages() {
		$messages = isset($this->options['messages']) ? $this->options['messages']:[];
		return $messages;
	}

	/**
	 * __call magic method.
	 * @param  string $name
	 * @param  array  $args
	 * @return string
	 */
	public function __call($name, array $args) {
		return $this->render($name, isset($args[0]) ? $args[0]:[]);
	}

	/**
	 * Set default widget renderer.
	 * @param string|callable $widget
	 */
	public function setDefaultWidget($widget) {
		$this->widget = $widget;
	}

	/**
	 * Get field's label.
	 * @return string
	 */
	public function label() {
		return ucfirst(str_replace('_', ' ', $this->name));
	}

	/**
	 * Get field's HTML label tag.
	 * @return string
	 */
	public function labelTag() {
		return '<label for="'.$this->getID().'">'.$this->label().'</label>';
	}

	/**
	 * Use the default renderer.
	 * @param  array $options
	 * @return string|Widget
	 */
	public function def(array $options=[]) {
		if(!$this->widget)
			throw new \Exception('No default render function for this field');
		return $this->render($this->widget, $options);
	}

	/**
	 * Render with a custom renderer.
	 * @param  callable $render_callback
	 * @param  array $options
	 * @return string|Widget
	 */
	public function render($render_callback, array $options=[]) {
		return $this->parent->render($render_callback, $this, $options);
	}

	/**
	 * __toString magic method.
	 * @return string
	 */
	public function __toString() {
		return $this->def();
	}

	/**
	 * Get the value.
	 * @return mixed
	 */
	public function value() {
		return $this->value;
	}

	/**
	 * Set the parent.
	 * @param GroupInterface $parent
	 */
	public function setParent($parent) {
		$this->parent = $parent;
	}

	/**
	 * Get the parent.
	 * @return GroupInterface
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * Set the name.
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Set the value.
	 * @param mixed $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	/**
	 * Get all parents.
	 * @return array
	 */
	public function getParents() {
		return $this->parent->getParents();
	}

	/**
	 * Get field id.
	 * @return string
	 */
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

	/**
	 * Get the full name.
	 * @return string
	 */
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

	/**
	 * Set the errors.
	 * @param array $errors
	 */
	public function setErrors(array $errors) {
		$this->errors = $errors;
	}

	/**
	 * Return the first error.
	 * @return string
	 */
	public function error() {
		if(isset(array_values($this->errors)[0]))
			return array_values($this->errors)[0];
	}

	/**
	 * Return all errors.
	 * @return array
	 */
	public function errors() {
		return $this->errors;
	}
}