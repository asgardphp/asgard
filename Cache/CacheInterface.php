<?php
namespace Asgard\Cache;

interface CacheInterface {
	public function __construct($path=null);
	public function clear();
	public function get($identifier, $default=null);
	public function set($identifier, $var);
	public function delete($identifier);
}