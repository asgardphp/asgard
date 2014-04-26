<?php
namespace Asgard\Utils;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load($queue) {
		\Asgard\Core\App::instance()->register('flash', function() { return new \Asgard\Utils\Flash; } );
		\Asgard\Core\App::instance()->register('memory', function() { return new \Asgard\Core\Memory; } );
		\Asgard\Core\App::instance()->register('html', function() { return new \Asgard\Utils\HTML; });
		// \Asgard\Core\App::instance()->register('locale', function() { return new \Asgard\Utils\Locale; } );
		
		parent::load($queue);
	}
}