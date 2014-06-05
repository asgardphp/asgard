<?php
namespace Asgard\Migration;

abstract class Migration {
	protected $app;

	public function __construct($app) {
		$this->app = $app;
	}
}