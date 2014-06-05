<?php
namespace Asgard\Email;

class Bundle extends \Asgard\Core\BundleLoader {
	public function buildApp($app) {
		$app->register('email', function($app) {
			$emailDriver = '\\'.trim($app['config']['email.driver'], '\\');
			$email = new $emailDriver();
			$email->transport($app['config']['email']);
			return $email;
		);
	}
}