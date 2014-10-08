<?php
namespace Asgard\Validation;

class ValidatorFactory implements ValidatorFactoryInterface {
	/**
	 * @var RulesRegistry
	 */
	protected $rulesRegistry;

	/**
	 * Constructor.
	 * @param RulesRegistry $rulesRegistry
	 */
	public function __construct(RulesRegistry $rulesRegistry=null) {
		$this->rulesRegistry = $rulesRegistry;
	}

	/**
	 * {@inheritDoc}
	 * @return Validator
	 */
	public function create() {
		return new Validator($this->rulesRegistry);
	}
}