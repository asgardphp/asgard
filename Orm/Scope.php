<?php
namespace Asgard\Orm;

class Scope {
	protected $cb;

	public function __construct($cb) {
		$this->cb = $cb;
	}

	public function process($orm) {
		$cb = $this->cb;
		$cb($orm);
	}
}