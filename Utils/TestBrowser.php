<?php
namespace Asgard\Utils;

class TestBrowser extends \Asgard\Http\Browser\Browser {
	protected $dst;

	public function __construct($app, $dst) {
		parent::__construct($app);
		$this->dst = $dst;
	}

	public function req(
		$url='',
		$method='GET',
		array $post=array(),
		array $file=array(),
		$body='',
		array $headers=array()
	) {
		file_put_contents($this->dst, $url."\n", FILE_APPEND);
		return parent::req($url, $method, $post, $file, $body, $headers);
	}
}