<?php
namespace Coxis\Core\Properties;

class LongTextProperty extends BaseProperty {
	public function getSQLType() {
		return 'text';
	}
}