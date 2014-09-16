<?php
namespace Asgard\Form;

/**
 * Manage widgets available for form fields.
 */
class WidgetsManager {
	/**
	 * Default instance.
	 * @var WidgetsManager
	 */
	protected static $instance;
	/**
	 * Widgets registry.
	 * @var array
	 */
	protected $widgets = [];
	/**
	 * Namespaces registry.
	 * @var [type]
	 */
	protected $namespaces = [
		'Asgard\Form\Widgets'
	];

	/**
	 * Singleton.
	 * @return WidgetsManager
	 */
	public static function singleton() {
		if(!static::$instance)
			static::$instance = new static;
		return static::$instance;
	}

	/**
	 * Register a widget.
	 * @param string          $widget
	 * @param string|callable $mixed  Widget class or callback
	 */
	public function setWidget($widget, $mixed) {
		$this->widgets[$widget] = $mixed;
	}

	/**
	 * Return a widget.
	 * @param  string $widget
	 * @return string|callable  Widget class or callback
	 */
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

	/**
	 * Add a namespace.
	 * @param string $namespace
	 */
	public function addNamespace($namespace) {
		$this->namespaces[] = trim($namespace, '\\');
		return $this;
	}
}