<?php
namespace Asgard\Entity\Properties;

/**
 * Email Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class EmailProperty extends TextProperty {
	/**
	 * {@inheritDoc}
	 */
	public function getSQLType() {
		return 'varchar(250)';
	}
}