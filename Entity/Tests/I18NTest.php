<?php
namespace Asgard\Entity\Tests;

class I18NTest extends \PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		$entityManager = new \Asgard\Entity\EntityManager;
		$entityManager->setValidatorFactory(new \Asgard\Validation\ValidatorFactory(new \Asgard\Validation\RulesRegistry));
		#set the EntityManager static instance for activerecord-like entities (e.g. new Article or Article::find())
		\Asgard\Entity\EntityManager::setInstance($entityManager);
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
			'id' => null,
			'title' => [
				'en' => 'Hello',
				'fr' => 'Bonjour'
			]
		], $post->toArrayRawI18N());

		$this->assertEquals([
			'id' => null,
			'title' => [
				'en' => 'Hello',
				'fr' => 'Bonjour'
			]
		], $post->toArrayI18N());

		$this->assertEquals(json_encode([
			'id' => null,
			'title' => [
				'en' => 'Hello',
				'fr' => 'Bonjour'
			]
		]), $post->toJSONI18N());

		$this->assertEquals(json_encode([[
			'id' => null,
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
			'id' => null,
			'titles' => [
				'en' => ['Hello'],
				'fr' => ['Bonjour']
			]
		], $post->toArrayRawI18N());

		$this->assertEquals([
			'id' => null,
			'titles' => [
				'en' => ['Hello'],
				'fr' => ['Bonjour']
			]
		], $post->toArrayI18N());

		$this->assertEquals(json_encode([
			'id' => null,
			'titles' => [
				'en' => ['Hello'],
				'fr' => ['Bonjour']
			]
		]), $post->toJSONI18N());

		$this->assertEquals(json_encode([[
			'id' => null,
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