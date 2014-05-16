<?php
namespace Asgard\Entity\Properties;

class DoubleProperty extends \Asgard\Entity\Property {
	public function getSQLType() {
		return 'double';
	}
}