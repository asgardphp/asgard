<?php
namespace Asgard\Entity\Tests;

class I18NTest extends \PHPUnit_Framework_TestCase {
	protected static $app;

	public static function setUpBeforeClass() {
		$app = new \Asgard\Container\Container;
		$app['config'] = new \Asgard\Config\Config;
		$app['config']->set('locale', 'en');
		$app['config']->set('locales', ['fr', 'en']);
		$app['hooks'] = new \Asgard\Hook\HooksManager($app);
		$app['cache'] = new \Asgard\Cache\NullCache;
		$app['rulesregistry'] = new \Asgard\Validation\RulesRegistry;
		$app['entitiesmanager'] = new \Asgard\Entity\EntitiesManager($app);
		\Asgard\Entity\Entity::setApp($app);
		static::$app = $app;
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