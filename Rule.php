<?php
namespace Asgard\Validation;

abstract class Rule {
	public function __construct() {}
	public function formatParameters(&$params) {}
}