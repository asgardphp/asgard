<?php
namespace Asgard\Orm;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load($queue) {
		$queue->addBundle(new \Asgard\Db\Bundle);
		
		parent::load($queue);
	}
}