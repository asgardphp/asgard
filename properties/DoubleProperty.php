<?php
namespace Coxis\Core\Properties;

class DoubleProperty extends BaseProperty {
	public function getSQLType() {
		return 'double';
	}
}