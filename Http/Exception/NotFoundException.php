<?php
namespace Asgard\Http\Exception;

/**
 * NotFound (404) exceptions.
 * @author Michel Hognerud <michel@hognerud.com>
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