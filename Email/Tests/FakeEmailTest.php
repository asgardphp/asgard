<?php
namespace Asgard\Email\Tests;

class FakeEmailTest extends \PHPUnit_Framework_TestCase {
	public function test() {
		if(file_exists(__DIR__.'/res.txt'))
			unlink(__DIR__.'/res.txt');

		$email = new \Asgard\Email\FakeEmail;
		$email->transport(['file'=>__DIR__.'/res.txt']);
		$email->send(function($msg) {
			$msg->text('hello!');
		});

		$this->assertRegExp('/Message-ID: <[0-9a-z_]+?@swift.generated>'."\n".
'Date: .*'."\n".
'From: '."\n".
'MIME-Version: 1.0'."\n".
'Content-Type: multipart\/alternative;'."\n".
' boundary="_=_swift_v4_[0-9a-z_]+?_=_"'."\n".
"\n".
'--_=_swift_v4_[0-9a-z_]+?_=_'."\n".
'Content-Type: text\/plain; charset=utf-8'."\n".
'Content-Transfer-Encoding: quoted-printable'."\n".
"\n".
'hello!'."\n".
"\n".
'--_=_swift_v4_[0-9a-z_]+?_=_--'."\n/", $this->normalize(file_get_contents(__DIR__.'/res.txt')));
	}

	protected function normalize($s) {
	    $s = str_replace("\r\n", "\n", $s);
	    $s = str_replace("\r", "\n", $s);
	    $s = preg_replace("/\n{2,}/", "\n\n", $s);
	    return $s;
	}
}