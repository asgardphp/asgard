<?php
namespace Asgard\Http\Annotations;

/**
* @Annotation
* @Target({"METHOD"})
*/
class Route {
	public $host;
	public $requirements;
	public $method;
	public $name;
	public $value;
	public $src;
}