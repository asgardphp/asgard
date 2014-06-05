<?php
namespace Asgard\Validation;

abstract class Rule {
	protected $handleEach = false;
	public function __construct() {}
	public function formatParameters(array &$params) {}
	public function getMessage() {}
	public function isHandlingEach() { return $this->handleEach; }
	public function handleEach($handleEach) { $this->handleEach = $handleEach; }
}