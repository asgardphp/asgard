<?php
namespace Asgard\Email;

interface DriverInterface {
	public function transport($transport);
	public function send($cb);
}