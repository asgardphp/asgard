<?php
namespace Asgard\Entity\Properties;

class DoubleProperty extends BaseProperty {
	public function getSQLType() {
		return 'double';
	}
}