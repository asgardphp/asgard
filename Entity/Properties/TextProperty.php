<?php
namespace Asgard\Entity\Properties;

class TextProperty extends \Asgard\Entity\Property {
	public function getSQLType() {
		if($this->get('length'))
			return 'varchar('.$this->get('length').')';
		else
			return 'varchar(255)';
	}
}