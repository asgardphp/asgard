<?php
namespace Asgard\Common;

/**
 * Date.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Date extends Datetime {
	/**
	 * Returns a date string.
	 * @return string
	 */
	public function __toString() {
		return $this->carbon->format('Y-m-d');
	}
}