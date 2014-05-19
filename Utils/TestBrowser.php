<?php
namespace Asgard\Utils;

class TestBrowser extends \Asgard\Utils\Browser {
	public function req(
		$url='',
		$method='GET',
		array $post=array(),
		array $file=array(),
		$body='',
		array $headers=array()
	) {
		// file_put_contents(_DIR_.'tests/tested.txt', $url."\n", FILE_APPEND);
		return parent::req($url, $method, $post, $file, $body, $headers);
	}
}