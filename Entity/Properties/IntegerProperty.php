<?php
namespace Asgard\Entity\Properties;

class IntegerProperty extends \Asgard\Entity\Property {
	public function getSQLType() {
		if($this->get('length'))
			return 'int('.$this->get('length').')';
		else
			return 'int(11)';
	}
}