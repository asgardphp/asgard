<?php
namespace Asgard\Form;

/**
 * Widget. Render a field.
 */
abstract class Widget {
	/**
	 * Label.
	 * @var string
	 */
	protected $label;
	/**
	 * Field
	 * @var Field
	 */
	public $field;
	/**
	 * Name.
	 * @var string
	 */
	protected $name;
	/**
	 * Value.
	 * @var mixed
	 */
	protected $value;
	/**
	 * Options.
	 * @var array
	 */
	protected $options;
	/**
	 * Parent form.
	 * @var Form
	 */
	protected $form;

	/**
	 * Constructor.
	 * @param string $name
	 * @param mixed  $value
	 * @param array  $options
	 * @param Form   $form
	 */
	public function __construct($name, $value=null, array $options=[], $form=null) {
		$this->name = $name;
		$this->value = $value;
		$this->form = $form;

		if(isset($options['label']))
			$this->label = $options['label'];
		if(isset($options['field'])) {
			$this->field = $options['field'];
			if($this->field->error()) {
				if(isset($options['attrs']['class']))
					$options['attrs']['class'] .= ' error';
				else
					$options['attrs']['class'] = 'error';
			}
		}
		$this->options = $options;
	}

	/**
	 * __toString magic method.
	 * @return string
	 */
	public function __toString() {
		return $this->render();
	}

	/**
	 * Return the label.
	 * @return string
	 */
	public function label() {
		return $this->label;
	}

	/**
	 * Set the label.
	 * @param string $label
	 */
	public function setLabel($label) {
		$this->label = $label;
	}
}