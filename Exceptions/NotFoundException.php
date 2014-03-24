<?php
namespace Asgard\Core\Exceptions;

class NotFoundException extends \Asgard\Core\ControllerException {
	function __construct($msg='') {
		$response = new \Asgard\Core\Response(404);
		parent::__construct($msg, $response);
	}
}