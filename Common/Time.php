<?php
namespace Asgard\Common;

/**
 * Time.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Time extends Datetime {
	/**
	 * Returns a date string.
	 * @return string
	 */
	public function __toString() {
		return $this->carbon->format('H:i:s');
	}
}