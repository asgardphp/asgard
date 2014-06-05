<?php
namespace Asgard\Form;

class Bundle extends \Asgard\Core\BundleLoader {
	public function buildApp($app) {
		$app->register('form', function($app, $args) {
			if(!isset($args[0]))
				$args[0] = null;
			if(!isset($args[1]))
				$args[1] = array();
			if(!isset($args[2]))
				$args[2] = array();
			if(!isset($args[3]))
				$args[3] = null;
			if(!isset($args[4]))
				$args[4] = $app;
			return new \Asgard\Form\Form($args[0], $args[1], $args[2], $args[3], $args[4]);
		});
	}
}