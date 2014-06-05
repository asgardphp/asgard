<?php
namespace Asgard\Entity\Properties;

class LongtextProperty extends \Asgard\Entity\Property {
	public function getSQLType() {
		return 'text';
	}
}