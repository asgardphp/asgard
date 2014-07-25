<?php
namespace Asgard\Entity\Tests;

class I18NTest extends \PHPUnit_Framework_TestCase {
	protected static $container;

	public static function setUpBeforeClass() {
		$container = new \Asgard\Container\Container;
		$container['config'] = new \Asgard\Config\Config;
		$container['config']->set('locale', 'en');
		$container['config']->set('locales', ['fr', 'en']);
		$container['hooks'] = new \Asgard\Hook\HooksManager($container);
		$container['cache'] = new \Asgard\Cache\NullCache;
		$container['rulesregistry'] = new \Asgard\Validation\RulesRegistry;
		$container['entitiesmanager'] = new \Asgard\Entity\EntitiesManager($container);
		\Asgard\Entity\Entity::setContainer($container);
		static::$container = $container;
	}

	public function test() {
		$post = new Fixtures\Post([], 'en');

		$post->title = 'Hello';

		#set and get
		$post->set('title', 'Bonjour', 'fr');
		$this->assertEquals('Hello', $post->title);
		$this->assertEquals('Hello', $post->get('title', 'en'));
		$this->assertEquals('Bonjour', $post->get('title', 'fr'));

		#translate
		$postFR = $post->translate('fr');
		$this->assertEquals('Bonjour', $postFR->title);
		$this->assertEquals('Hello', $postFR->get('title', 'en'));
		$this->assertEquals('Bonjour', $postFR->get('title', 'fr'));

		#arrays
		$this->assertEquals([
			'title' => [
				'en' => 'Hello',
				'fr' => 'Bonjour'
			]
		], $post->toArrayRawI18N());

		$this->assertEquals([
			'title' => [
				'en' => 'Hello',
				'fr' => 'Bonjour'
			]
		], $post->toArrayI18N());

		$this->assertEquals(json_encode([
			'title' => [
				'en' => 'Hello',
				'fr' => 'Bonjour'
			]
		]), $post->toJSONI18N());

		$this->assertEquals(json_encode([[
			'title' => [
				'en' => 'Hello',
				'fr' => 'Bonjour'
			]
		]]), \Asgard\Entity\Entity::arrayToJSONI18N([$post]));

		#validation
		$post->set('title', 'a', 'fr');

		$this->assertTrue($post->valid());
		$this->assertFalse($post->validI18N(['fr', 'en']));

		$this->assertEquals([], $post->errors());
		$this->assertEquals([
			'title' => [
				'fr' => 'Title is too short.'
			]
		], $post->errorsI18N(['fr', 'en']));
	}

	public function testMultiple() {
		$post = new Fixtures\PostMultiple([], 'en');

		$post->titles = ['Hello'];

		#set and get
		$post->set('titles', ['Bonjour'], 'fr');
		$this->assertEquals(['Hello'], $post->titles->all());
		$this->assertEquals(['Hello'], $post->get('titles', 'en')->all());
		$this->assertEquals(['Bonjour'], $post->get('titles', 'fr')->all());

		#translate
		$this->assertEquals(['Hello'], $post->titles->all());
		$this->assertEquals(['Hello'], $post->get('titles', 'en')->all());
		$this->assertEquals(['Bonjour'], $post->get('titles', 'fr')->all());

		#arrays
		$this->assertEquals([
			'titles' => [
				'en' => ['Hello'],
				'fr' => ['Bonjour']
			]
		], $post->toArrayRawI18N());

		$this->assertEquals([
			'titles' => [
				'en' => ['Hello'],
				'fr' => ['Bonjour']
			]
		], $post->toArrayI18N());

		$this->assertEquals(json_encode([
			'titles' => [
				'en' => ['Hello'],
				'fr' => ['Bonjour']
			]
		]), $post->toJSONI18N());

		$this->assertEquals(json_encode([[
			'titles' => [
				'en' => ['Hello'],
				'fr' => ['Bonjour']
			]
		]]), \Asgard\Entity\Entity::arrayToJSONI18N([$post]));

		#validation
		$post->set('titles', ['a'], 'fr');

		$this->assertTrue($post->valid());
		$this->assertFalse($post->validI18N(['fr', 'en']));

		$this->assertEquals([], $post->errors());
		$this->assertEquals([
			'titles' => [
				'fr' => 'Fr is not valid.'
			]
		], $post->errorsI18N(['fr', 'en']));
	}
}