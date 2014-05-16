<?php
namespace Asgard\Xpath\Tests;

class XpathTest extends \PHPUnit_Framework_TestCase {
	public function test1() {
		$html = file_get_contents(__DIR__.'/page.html');
		$doc = new \Asgard\Xpath\Doc($html);

		$this->assertInstanceOf('DOMXpath', $doc->getXpath());
		$this->assertEquals($html, $doc->getCode());
		$this->assertEquals('951b0c5e212ec90ed70895ac10a5dfac2025ee9e', sha1($doc->html()));

		$this->assertEquals('<a href="#">Home</a>', $doc->html('/html/body/div[1]/div/div[2]/ul/li'));
		$this->assertEquals('<a href="#about">About</a>', $doc->html('/html/body/div[1]/div/div[2]/ul/li', 1));

		$this->assertEquals('Bootstrap starter template', $doc->text('/html/body/div[2]/div/h1'));
		$this->assertEquals('About', $doc->text('/html/body/div[1]/div/div[2]/ul/li', 1));

		$this->assertInstanceOf('Asgard\Xpath\Node', $doc->item('/html/body/div[1]/div/div[2]/ul/li'));
		$this->assertInstanceOf('DOMElement', $doc->item('/html/body/div[1]/div/div[2]/ul/li')->getNode());
		$this->assertInstanceOf('Asgard\Xpath\Node', $doc->item('/html/body/div[1]/div/div[2]/ul/li', 1));
		$this->assertInstanceOf('DOMElement', $doc->item('/html/body/div[1]/div/div[2]/ul/li', 1)->getNode());
		$this->assertInstanceOf('Asgard\Xpath\Node', $doc->item('/html/body/div[1]/div/div[2]/ul/li', 5));
		$this->assertNull($doc->item('/html/body/div[1]/div/div[2]/ul/li', 5)->getNode());
		$this->assertInstanceOf('DOMXPath', $doc->item('/html/body/div[1]/div/div[2]/ul/li')->getXpath());

		$this->assertEquals($doc->html('/html/body/div[1]/div/div[2]/ul/li'), $doc->item('/html/body/div[1]/div')->html('div[2]/ul/li'));
		$this->assertEquals($doc->html('/html/body/div[1]/div/div[2]/ul/li', 1), $doc->item('/html/body/div[1]/div')->html('div[2]/ul/li', 1));

		$this->assertEquals($doc->text('/html/body/div[2]/div/h1'), $doc->item('/html/body/div[2]')->text('div/h1'));
		$this->assertEquals($doc->text('/html/body/div[1]/div/div[2]/ul/li', 1), $doc->item('/html/body/div[1]/div')->text('div[2]/ul/li', 1));

		$this->assertEquals('#about', $doc->item('/html/body/div[1]/div/div[2]/ul/li[2]/a')->getAttribute('href'));

		$items = $doc->items('/html/body/div[1]/div/div[2]/ul/li');
		$this->assertCount(3, $items);
		$this->assertEquals('Home', $items[0]->text());

		$item = $doc->item('/html/body/div[1]/div/div[2]/ul/li[2]');
		$this->assertEquals('Home', $item->prev()->text());
		$this->assertEquals('Contact', $item->next()->text());
	}
}