<?php
namespace Asgard\Form;

/**
 * Group of fieldsor sub-groups.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface GroupInterface extends \ArrayAccess, \Iterator {
	/**
	 * Create a validator.
	 * @return \Asgard\Validation\ValidatorInterface
	 */
	public function createValidator();

	/**
	 * Get the translator.
	 * @return \Symfony\Component\Translation\TranslatorInterface
	 */
	public function getTranslator();

	/**
	 * Get the request from group or a parent.
	 * @return \Asgard\Http\Request
	 */
	public function getRequest();

	/**
	 * Return the name.
	 * @return string
	 */
	public function name();

	/**
	 * Set the name.
	 * @param string $name
	 */
	public function setName($name);

	/**
	 * Return the number of fields.
	 * @return integer
	 */
	public function size();

	/**
	 * Check if group has a file.
	 * @return boolean true if has file
	 */
	public function hasFile();

	/**
	 * Return a new widget instance.
	 * @param  string|callable $widget Widget class or callback.
	 * @param  string          $name   string
	 * @param  mixed           $value
	 * @param  array           $options
	 * @return Widget
	 */
	public function getWidget($widget, $name, $value, array $options=[]);

	/**
	 * Return the widgets manager.
	 * @return WidgetManagerInterface
	 */
	public function getWidgetManager();

	/**
	 * Set the widgets manager.
	 * @param WidgetManager $WidgetManager
	 */
	public function setWidgetManager(WidgetManager $WidgetManager);

	/**
	 * Render a field.
	 * @param  string|callable $render_callback
	 * @param  Field           $field
	 * @param  array           $options
	 * @return Widget
	 */
	public function render($render_callback, $field, array $options=[]);

	/**
	 * Check if group is valid.
	 * @return boolean true if valid
	 */
	public function isValid();

	/**
	 * Check if group's form was sent.
	 * @return boolean true if sent
	 */
	public function sent();

	/**
	 * Return errors.
	 * @return array
	 */
	public function errors();

	/**
	 * Remove a field.
	 * @param  string $name
	 */
	public function remove($name);

	/**
	 * Return a field.
	 * @param  string $name
	 * @return Field|Group
	 */
	public function get($name);

	/**
	 * Add a field.
	 * @param Field|GroupInterface $field
	 * @param string               $name
	 */
	public function add($field, $name=null);

	/**
	 * Check if has a field.
	 * @param  string  $field_name
	 * @return boolean
	 */
	public function has($field_name);

	/**
	 * Reset fields.
	 * @return GroupInterface $this
	 */
	public function resetFields();

	/**
	 * Return all fields.
	 * @return array
	 */
	public function fields();

	/**
	 * Add fields.
	 * @param array $fields
	 */
	public function addFields(array $fields);

	/**
	 * Reset data.
	 * @return GroupInterface $this
	 */
	public function reset();

	/**
	 * Set data.
	 * @param array $data
	 */
	public function setData(array $data);

	/**
	 * Return data.
	 * @return array
	 */
	public function data();

	/**
	 * Set parent.
	 * @param  GroupInterface $parent
	 * @return GroupInterface $this
	 */
	public function setParent(GroupInterface $parent);

	/**
	 * Get top parent form.
	 * @return GroupInterface
	 */
	public function getTopForm();

	/**
	 * Set fields.
	 * @param array $fields
	 */
	public function setFields(array $fields);

	/**
	 * Get all parents.
	 * @return array
	 */
	public function getParents();

	/**
	 * Actually perform the group saving. Empty by default but can be overriden.
	 */
	public function doSave();
}