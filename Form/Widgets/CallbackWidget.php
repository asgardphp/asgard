<?php
namespace Asgard\Form\Widgets;

/**
 * Callback widget.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class CallbackWidget extends \Asgard\Form\Widget {
	/**
	 * Callback.
	 * @var callable
	 */
	protected $cb;

	/**
	 * Constructor.
	 * @param callable                   $cb
	 * @param string                     $name
	 * @param mixed                      $value
	 * @param array                      $options
	 * @param \Asgard\Form\FormInterface $form
	 */
	public function __construct(callable $cb, $name, $value=null, array $options=[], $form=null) {
		parent::__construct($name, $value, $options, $form);
		$this->cb = $cb;
	}

	/**
	 * {@inheritDoc}
	 */
	public function render(array $options=[]) {
		$options = $this->options+$options;
		$cb = $this->cb;
		return $cb($this->field, $options);
	}
}