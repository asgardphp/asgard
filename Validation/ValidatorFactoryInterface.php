<?php
namespace Asgard\Validation;

interface ValidatorFactoryInterface {
	/**
	 * Create a new instance.
	 * @param  integer            $total
	 * @param  integer            $page
	 * @param  integer            $per_page
	 * @return ValidatorInterface
	 */
	public function create();
}