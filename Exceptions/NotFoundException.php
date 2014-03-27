<?php
namespace Asgard\Core\Exceptions;

class NotFoundException extends \Asgard\Core\ControllerException {
	public function __construct($msg='') {
		$response = new \Asgard\Core\Response(404);
		parent::__construct($msg, $response);
	}
}