<?php
namespace Asgard\Http\Annotations;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @author Michel Hognerud <michel@hognerud.com>
*/
class Route {
	public $host;
	public $requirements;
	public $method;
	public $name;
	public $value;
	public $src;
}