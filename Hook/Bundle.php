<?php
namespace Asgard\Hook;

/**
 * The hook bundle.
 * 
 * @author Michel Hognerud <michel@hognerud.net>
*/
class Bundle extends \Asgard\Core\BundleLoader {
	/**
	 * {@inheritdoc}
	*/
	public function buildApp($app) {
		$app->register('hooks', function($app) { return new \Asgard\Hook\HooksManager($app); } );
	}
}