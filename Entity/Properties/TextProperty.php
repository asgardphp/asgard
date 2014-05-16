<?php
namespace Asgard\Entity\Properties;

class TextProperty extends BaseProperty {
	public function getSQLType() {
		if($this->length)
			return 'varchar('.$this->length.')';
		else
			return 'varchar(255)';
	}
}