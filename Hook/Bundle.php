<?php
namespace Asgard\Hook;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load(\Asgard\Core\BundlesManager $queue) {
		parent::load($queue);
	}
}