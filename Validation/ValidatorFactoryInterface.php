<?php
namespace Asgard\Validation;

/**
 * Validator factory interface.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface ValidatorFactoryInterface {
	/**
	 * Create a new instance.
	 * @return ValidatorInterface
	 */
	public function create();
}