<?php
namespace Asgard\Http\Exceptions;

class NotFoundException extends \Asgard\Http\ControllerException {
	public function __construct($msg='') {
		parent::__construct(404, $msg);
	}
}