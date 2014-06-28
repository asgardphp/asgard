<?php
namespace Asgard\Form;

class WidgetsManager {
	protected static $instance;
	protected $widgets = [];
	protected $namespaces = [
		'Asgard\Form\Widgets'
	];

	public static function singleton() {
		if(!static::$instance)
			static::$instance = new static;
		return static::$instance;
	}

	public function setWidget($widget, $mixed) {
		$this->widgets[$widget] = $mixed;
	}

	public function getWidget($widget) {
		if(isset($this->widgets[$widget]))
			return $this->widgets[$widget];
		else {
			foreach(array_reverse($this->namespaces) as $namespace) {
				if(class_exists('\\'.$namespace.'\\'.ucfirst($widget).'Widget'))
					return '\\'.$namespace.'\\'.ucfirst($widget).'Widget';
			}
		}
	}

	public function addNamespace($namespace) {
		$this->namespaces[] = trim($namespace, '\\');
		return $this;
	}
}