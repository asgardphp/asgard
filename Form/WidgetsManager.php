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
	 * @param  string|callable $widget
	 * @return Widget Widget class or callback
	 */
	public function getWidget($widget, $name, $value, array $options, Form $form) {
		if(is_string($widget)) {
			if(isset($this->widgets[$widget]))
				$widget = $this->widgets[$widget];
			else {
				foreach(array_reverse($this->namespaces) as $namespace) {
					if(class_exists('\\'.$namespace.'\\'.ucfirst($widget).'Widget')) {
						$widget = '\\'.$namespace.'\\'.ucfirst($widget).'Widget';
						break;
					}
				}
			}
			if(!isset($widget))
				return;
		}
		elseif(!is_callable($widget))
			throw new \Exception('Invalid widget type.');
		return $this->getWidgetInstance($widget, $name, $value, $options, $form);
	}

	/**
	 * Get a widget instance.
	 * @param  string|callable $widget
	 * @param  string          $name
	 * @param  mixed           $value
	 * @param  array           $options
	 * @return Widget
	 */
	public function getWidgetInstance($widget, $name, $value, array $options, Form $form) {
		if(is_string($widget)) {
			$reflector = new \ReflectionClass($widget);
			return $reflector->newInstanceArgs([$name, $value, $options, $form]);
		}
		elseif(is_callable($widget))
			return new Widgets\CallbackWidget($widget, $name, $value, $options, $form);
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