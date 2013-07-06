<?php
namespace Coxis\Form\Widgets;

abstract class HTMLWidget {
	protected $label;
	protected $field;
	protected $name;
	protected $value;
	protected $options;
	// protected $attrs = array(); #todo necessary?

	function __construct($name, $value=null, $options=array()) {
		$this->name = $name;
		$this->value = $value;

		if(isset($options['label']))
			$this->label = $options['label'];
		if(isset($options['field'])) {
			$this->field = $options['field'];
			if($this->field->getError())
				if(isset($options['attrs']['class']))
					$options['attrs']['class'] .= ' error';
				else
					$options['attrs']['class'] = 'error';
		}
		$this->options = $options;
	}

	public static function __callStatic($name, $args) {
		// $widget = new $name.'Field';
		$reflector = new \ReflectionClass($name.'Widget');
		$widget = $reflector->newInstanceArgs($args);
		return $widget;
	}

	public function __toString() {
		return $this->render();
	}

	public function getLabel() {
		return $this->label;
	}

	public function setLabel($label) {
		$this->label = $label;
	}
}