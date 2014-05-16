<?php
namespace Asgard\Entity\Properties;

class LongTextProperty extends BaseProperty {
	public function getSQLType() {
		return 'text';
	}
}