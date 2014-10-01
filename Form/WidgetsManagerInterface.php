<?php
namespace Asgard\Form;

/**
 * Manage widgets available for form fields.
 */
interface WidgetsManagerInterface {
	/**
	 * Register a widget.
	 * @param string          $widget
	 * @param string|callable $mixed  Widget class or callback
	 */
	public function setWidget($widget, $mixed);

	/**
	 * Return a widget.
	 * @param  string|callable $widget
	 * @return Widget Widget class or callback
	 */
	public function getWidget($widget, $name, $value, array $options, FormInterface $form);

	/**
	 * Get a widget instance.
	 * @param  string|callable $widget
	 * @param  string          $name
	 * @param  mixed           $value
	 * @param  array           $options
	 * @return Widget
	 */
	public function getWidgetInstance($widget, $name, $value, array $options, FormInterface $form);

	/**
	 * Add a namespace.
	 * @param string $namespace
	 */
	public function addNamespace($namespace);
}