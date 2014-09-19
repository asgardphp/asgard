<?php
namespace Asgard\Entity\Properties;

/**
 * Email Property.
 */
class EmailProperty extends TextProperty {
	/**
	 * {@inheritdoc}
	 */
	public function getSQLType() {
		return 'varchar(250)';
	}
}