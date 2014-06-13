<?php
namespace Asgard\Form;

abstract class Widget {
	protected $label;
	public $field;
	public $group;
	protected $name;
	protected $value;
	protected $options;
	protected $form;
	protected static $app;

	public function __construct($name, $value=null, array $options=[], $form=null) {
		$this->name = $name;
		$this->value = $value;
		$this->form = $form;

		if(isset($options['label']))
			$this->label = $options['label'];
		if(isset($options['field'])) {
			$this->field = $options['field'];
			if($this->field->getError()) {
				if(isset($options['attrs']['class']))
					$options['attrs']['class'] .= ' error';
				else
					$options['attrs']['class'] = 'error';
			}
		}
		$this->options = $options;
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