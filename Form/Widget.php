<?php
namespace Asgard\Form;

/**
 * Widget. Render a field.
 * @author Michel Hognerud <michel@hognerud.com>
 */
abstract class Widget {
	/**
	 * Label.
	 * @var string
	 */
	protected $label;
	/**
	 * Field
	 * @var Field|Group
	 */
	protected $field;
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
	 * @var FormInterface
	 */
	protected $form;

	/**
	 * Constructor.
	 * @param string        $name
	 * @param mixed         $value
	 * @param array         $options
	 * @param FormInterface $form
	 */
	public function __construct($name, $value=null, array $options=[], FormInterface $form=null) {
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
		try {
		return $this->render();
	}catch(\Exception $e) {
		d($e);
	}
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

	/**
	 * Render the widget.
	 * @param  array $options
	 * @return string
	 */
	abstract public function render(array $options=[]);
}