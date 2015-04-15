<?php
namespace Asgard\Form\Fields;

/**
 * Country field.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class CountryField extends SelectField {
	protected $intl;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(array $options=[], \Asgard\Common\Intl $intl=null) {
		parent::__construct($options);
		$this->intl = $intl;
		if(!isset($this->options['choices']) || !$this->options['choices'])
			$this->options['choices'] = $this->getIntl()->getCountryNames();
	}

	public function seIntl($intl) {
		$this->intl = $intl;
	}

	protected function getIntl() {
		if(!$this->intl)
			$this->intl = \Asgard\Common\Intl::singleton();
		return $this->intl;
	}
}