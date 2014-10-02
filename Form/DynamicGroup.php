<?php
namespace Asgard\Form;

/**
 * Group with undefined number of fields.
 */
class DynamicGroup extends Group {
	/**
	 * Callback to create new fields.
	 * @var callable
	 */
	protected $cb;
	/**
	 * Callback to render fields.
	 * @var callable
	 */
	protected $default_render;

	#todo name?
	/**
	 * Constructor.
	 * @param callable $cb
	 * @param callable $default_render
	 */
	public function __construct($cb=null, $default_render=null) {
		$this->cb = $cb;
		$this->default_render = $default_render;
	}

	/**
	 * Set callback.
	 * @param callable $cb
	 */
	public function setCallback($cb) {
		$this->cb = $cb;
	}

	/**
	 * Set group data.
	 * @param array $data
	 * @return DynamicGroupInterface $this
	 */
	public function setData(array $data) {
		$this->data = array_values($data);

		$this->resetFields();

		foreach($data as $name=>$data)
			$this->newField($name, $data);

		$this->updateChilds();

		return $this;
	}

	/**
	 * Set default renderer.
	 * @param callable $default_render
	 */
	public function setDefaultRender($default_render) {
		$this->default_render = $default_render;
	}

	/**
	 * Render a field or a sub-group.
	 * @param  Group|Field $field
	 * @return string|Widget
	 */
	public function field($field=null) {
		$default_render = $this->default_render;
		if($default_render !== null)
			return $default_render($field);
		elseif($field instanceof Field)
			return $field->def();
	}

	/**
	 * Render the javascript template.
	 * @param  string $offset
	 * @return string
	 */
	public function renderTemplate($offset='') {
		$randstr = \Asgard\Common\Tools::randstr(10);
		$jq = $this->renderNew('{{'.$randstr.'}}');
		$jq = addcslashes((string)$jq, "'");
		$jq = str_replace("\r\n", "\n", $jq);
		$jq = str_replace("\n", "\\\n", $jq);
		$jq = str_replace('{{'.$randstr.'}}', $offset, $jq);
		return $jq;
	}

	/**
	 * Create a new field or sub-group.
	 * @param  string $name
	 * @param  mixed $data
	 * @return Field|Group
	 */
	protected function newField($name=null, $data=null) {
		if($name !== null && isset($this[$name]))
			return;
		$cb = $this->cb;
		$newelement = $cb($data);
		if(!$newelement)
			return;
		$this->add($newelement, $name);
		return $newelement;
	}

	/**
	 * Render a new field or sub-group.
	 * @param  string $offset
	 * @return string|Widget
	 */
	protected function renderNew($offset=null) {
		$default_render = $this->default_render;

		if($offset === null)
			$offset = $this->size();

		if(!isset($this[$offset]))
			$field = $this->newField($offset);
		else
			$field = $this[$offset];
		if(!$field)
			return;

		if($default_render === null)
			$r = $this->renderField($field);
		else
			$r = $default_render($field);

		unset($this[$offset]);

		return $r;
	}

	/**
	 * Render a field with its own rendering.
	 * @param  Field $field
	 * @return string|Widget
	 */
	protected function renderField($field) {
		return $field->def();
	}
}