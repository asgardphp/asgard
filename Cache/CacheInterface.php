<?php
namespace Asgard\Cache;

/**
 * Cache wrapper.
 * @author Michel Hognerud <michel@hognerud.com>
 * @api
 */
interface CacheInterface extends \Doctrine\Common\Cache\Cache, \ArrayAccess {
}