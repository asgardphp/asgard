<?php
namespace Asgard\Entity\Properties;

/**
 * Email Property.
 */
class EmailProperty extends TextProperty {
	/**
	 * {@inheritDoc}
	 */
	public function getSQLType() {
		return 'varchar(250)';
	}
}