<?php
namespace Asgard\Entity\Properties;

class LongTextProperty extends \Asgard\Entity\Property {
	public function getSQLType() {
		return 'text';
	}
}