<?php
namespace Asgard\Form;

/**
 * Manage widgets available for form fields.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class WidgetManager implements WidgetManagerInterface {
	/**
	 * Default instance.
	 * @var WidgetManagerInterface
	 */
	protected static $instance;
	/**
	 * Widgets registry.
	 * @var array
	 */
	protected $widgets = [];
	/**
	 * Widgets registry.
	 * @var array
	 */
	protected $factories = [];
	/**
	 * Namespaces registry.
	 * @var array
	 */
	protected $namespaces = [
		'Asgard\Form\Widgets'
	];

	/**
	 * Singleton.
	 * @return WidgetManagerInterface
	 */
	public static function singleton() {
		if(!static::$instance)
			static::$instance = new static;
		return static::$instance;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setWidget($widget, $mixed) {
		$this->widgets[$widget] = $mixed;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getWidget($widget, $name, $value, array $options, FormInterface $form) {
		if(is_string($widget)) {
			if(isset($this->factories[$widget])) {
				$factory = $this->factories[$widget];
				return $factory->create($name, $value, $options, $form);
			}
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
	 * {@inheritDoc}
	 */
	public function getWidgetInstance($widget, $name, $value, array $options, FormInterface $form) {
		if(is_string($widget)) {
			$reflector = new \ReflectionClass($widget);
			return $reflector->newInstanceArgs([$name, $value, $options, $form]);
		}
		elseif(is_callable($widget))
			return new Widgets\CallbackWidget($widget, $name, $value, $options, $form);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addNamespace($namespace) {
		$this->namespaces[] = trim($namespace, '\\');
		return $this;
	}

	public function setWidgetFactory($name, \Asgard\Form\WidgetFactoryInterface $widgetFactory) {
		$this->factories[$name] = $widgetFactory;
		return $this;
	}
}