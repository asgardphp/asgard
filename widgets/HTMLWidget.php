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

	public static function getWidget($name, $args) {
		$reflector = new \ReflectionClass($name);
		$widget = $reflector->newInstanceArgs($args);
		return $widget;
	}

	public static function __callStatic($name, $args) {
		$widget = \Coxis\Core\Context::get('ioc')->get('Coxis\Form\Widgets\\'.$name, array(), 'Coxis\Form\Widgets\\'.$name.'Widget');
		$reflector = new \ReflectionClass($widget);
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