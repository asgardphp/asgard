<?php
namespace Asgard\Entity;

/**
 * Interface for entity exceptions.
 */
interface EntityExceptionInterface {
	/**
	 * Errors.
	 * @return array
	 */
	public function getErrors();
}