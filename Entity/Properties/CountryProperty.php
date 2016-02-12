<?php
namespace Asgard\Entity\Properties;

/**
 * Country Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class CountryProperty extends \Asgard\Entity\Property {
	protected $intl;

	/**
	 * {@inheritDoc}
	 */
	public function getFormParameters() {
		$params = parent::getFormParameters();
		$params['choices'] = $this->getIntl()->getCountryNames();
		return $params;
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepareValidator(\Asgard\Validation\ValidatorInterface $validator) {
		parent::prepareValidator($validator);
		$validator->rule('in', [array_keys($this->getIntl()->getCountryNames())]);
	}

	public function seIntl($intl) {
		$this->intl = $intl;
	}

	public function getIntl() {
		if(!$this->intl)
			$this->intl = \Asgard\Common\Intl::singleton();
		return $this->intl;
	}

	/**
	 * Return parameters for ORM.
	 * @return array
	 */
	public function getORMParameters() {
		return [
			'type' => 'string',
		];
	}

	/**
	 * Return prepared input for SQL.
	 * @param  mixed $val
	 * @return string
	 */
	public function toSQL($val) {
		return (string)$val;
	}

	/**
	 * Transform SQL output.
	 * @param  mixed $val
	 * @return boolean
	 */
	public function fromSQL($val) {
		return (string)$val;
	}
}