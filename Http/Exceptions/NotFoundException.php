<?php
namespace Asgard\Http\Exceptions;

/**
 * NotFound (404) exceptions.
 */
class NotFoundException extends \Asgard\Http\ControllerException {
	/**
	 * Constructor.
	 * @param string $msg
	 */
	public function __construct($msg='') {
		parent::__construct(404, $msg);
	}
}