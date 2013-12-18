<?php
namespace Coxis\Form\Widgets;

abstract class HTMLWidget {
	protected $label;
	protected $field;
	protected $name;
	protected $value;
	protected $options;

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
		return \Coxis\Core\App::instance()->make('Coxis\Form\Widgets\\'.$name, $args, function() use($name, $args) {
			$class = 'Coxis\Form\Widgets\\'.$name.'Widget';
			$reflector = new \ReflectionClass($class);
			$widget = $reflector->newInstanceArgs($args);
			return $widget;
		});
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