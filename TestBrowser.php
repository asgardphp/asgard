<?php
namespace Asgard\Utils;

class TestBrowser extends \Asgard\Utils\Browser {
	public function req(
		$url='',
		$method='GET',
		$post=array(),
		$file=array(),
		$body='',
		$headers=array()
	) {
		file_put_contents(_DIR_.'tests/tested.txt', $url."\n", FILE_APPEND);
		return parent::req($url, $method, $post, $file, $body, $headers);
	}
}