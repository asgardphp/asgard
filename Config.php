<?php
namespace Asgard\Tester;

class Config {
	public $ignore404 = false;
	public $generateStartingRequests = true;
	public $host = 'localhost';
	public $root = '';
	public $coverage = [];
	public $urls = [];
	public $exclusions = [];
	public $inclusions = [];

	public function init($crawler, $browser) {
	}

	public function each($browser) {

	}
}