<?php
namespace Coxis\Core\Exceptions;

class NotFoundException extends \Coxis\Core\ControllerException {
	function __construct($msg='') {
		$response = new \Coxis\Core\Response(404);
		parent::__construct($msg, $response);
	}
}