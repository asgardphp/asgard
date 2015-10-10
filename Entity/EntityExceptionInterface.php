<?php
namespace Asgard\Entity;

/**
 * Interface for entity exceptions.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface EntityExceptionInterface {
	/**
	 * Errors.
	 * @return array
	 */
	public function getErrors();
}