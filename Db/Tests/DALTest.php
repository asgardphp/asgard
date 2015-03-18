<?php
namespace Asgard\Db\Tests;

use Asgard\Db\DAL;

class DALTest extends \PHPUnit_Framework_TestCase {
	protected static $db;

	public static function setUpBeforeClass() {
		$config = [
			'driver' => 'sqlite',
			'database' => ':memory:',
		];
		$db = static::$db = new \Asgard\Db\DB($config);

		$schema = $db->getSchema();

		#Author
		$schema->create('author', function($table) {
			$table->addColumn('id', 'integer', [
					'length' => 4,
					'notnull' => true,
					'autoincrement' => true,
			]);
			$table->addColumn('name', 'string', [
					'length' => 255,
					'notnull' => true,
			]);

			$table->setPrimaryKey(['id']);
		});

		$db->dal()->into('author')->insert(['id'=>1, 'name'=>'Bob']);
		$db->dal()->into('author')->insert(['id'=>2, 'name'=>'Joe']);
		$db->dal()->into('author')->insert(['id'=>3, 'name'=>'John']);

		#News
		$schema->create('news', function($table) {
			$table->addColumn('id', 'integer', [
					'length' => 4,
					'notnull' => true,
					'autoincrement' => true,
			]);
			$table->addColumn('title', 'string', [
					'length' => 255,
			]);
			$table->addColumn('content', 'string', [
					'length' => 255,
			]);
			$table->addColumn('category_id', 'integer', [
					'length' => 11,
			]);
			$table->addColumn('author_id', 'integer', [
					'length' => 11,
			]);
			$table->addColumn('score', 'integer', [
					'length' => 11,
			]);

			$table->setPrimaryKey(['id']);
		});

		$db->dal()->into('news')->insert(['id'=>1, 'title'=>'Welcome!', 'content'=>'blabla', 'category_id'=>1, 'author_id'=>1, 'score'=>2]);
		$db->dal()->into('news')->insert(['id'=>2, 'title'=>'1000th visitor!', 'content'=>'blabla', 'category_id'=>1, 'author_id'=>2, 'score'=>5]);
		$db->dal()->into('news')->insert(['id'=>3, 'title'=>'Important', 'content'=>'blabla', 'category_id'=>2, 'author_id'=>1, 'score'=>1]);
	}

	protected static function getDAL() {
		$dal = new \Asgard\Db\DAL(static::$db);
		$dal->setPaginatorFactory(new \Asgard\Common\PaginatorFactory);
		return $dal;
	}

	public function testRawSQL() {
		$this->assertEquals('SELECT * FROM `news` WHERE `date`=NOW()', $this->getDAL()->from('news')->where('date', DAL::raw('NOW()'))->buildSQL());
		$this->assertEquals('INSERT INTO `news` (`date`) VALUES (NOW())', $this->getDAL()->into('news')->buildInsertSQL(['date'=>DAL::raw('NOW()')]));
		$this->assertEquals('UPDATE `news` SET `date`=NOW()', $this->getDAL()->from('news')->buildUpdateSQL(['date'=>DAL::raw('NOW()')]));
	}

	public function test1() {
		/* TABLES */
		$this->assertEquals('SELECT * FROM `news`', $this->getDAL()->from('news')->buildSQL());
		$this->assertEquals('SELECT * FROM `news` `n`', $this->getDAL()->from('news n')->buildSQL());
		$this->assertEquals('SELECT * FROM `news`, `category` `c`', $this->getDAL()->from('news, category c')->buildSQL());
		$this->assertEquals('SELECT * FROM `news`, `category` `c`', $this->getDAL()->from('news')->addFrom('category c')->buildSQL());
		$this->assertEquals('SELECT * FROM `news` `n`', $this->getDAL()->from('news n, category c')->removeFrom('c')->buildSQL());

		/* SELECT / FROM */
		$this->assertEquals('SELECT `title` FROM `news`', $this->getDAL()->from('news')->select('title')->buildSQL());
		$this->assertEquals('SELECT `title` AS `t` FROM `news`', $this->getDAL()->from('news')->select('title as t')->buildSQL());
		$this->assertEquals('SELECT COUNT(*) AS `c` FROM `news`', $this->getDAL()->from('news')->select('COUNT(*) as c')->buildSQL());
		$this->assertEquals('SELECT `id` AS `i`, `title` AS `t` FROM `news`', $this->getDAL()->from('news')->select('id as i, title as t')->buildSQL());
		$this->assertEquals('SELECT `id` AS `i` FROM `news`', $this->getDAL()->from('news')->select('id as i, title as t')->removeSelect('t')->buildSQL());
		$this->assertEquals('SELECT `id` AS `i`, `title` AS `t` FROM `news`', $this->getDAL()->from('news')->select('id as i')->addSelect('title as t')->buildSQL());

		/* OFFSET */
		$this->assertEquals('SELECT * FROM `news` LIMIT 10, '.PHP_INT_MAX, $this->getDAL()->from('news')->offset(10)->buildSQL());

		/* LIMIT */
		$this->assertEquals('SELECT * FROM `news` LIMIT 10', $this->getDAL()->from('news')->limit(10)->buildSQL());

		/* BOTH */
		$this->assertEquals('SELECT * FROM `news` LIMIT 10, 20', $this->getDAL()->from('news')->offset(10)->limit(20)->buildSQL());

		/* ORDER BY */
		$this->assertEquals('SELECT * FROM `news` ORDER BY COUNT(*) ASC', $this->getDAL()->from('news')->orderBy('COUNT(*) ASC')->buildSQL());
		$this->assertEquals('SELECT * FROM `news` ORDER BY `id` ASC', $this->getDAL()->from('news')->orderBy('id ASC')->buildSQL());
		$this->assertEquals('SELECT * FROM `news` ORDER BY `news`.`id` ASC', $this->getDAL()->from('news')->orderBy('news.id ASC')->buildSQL());
		$this->assertEquals('SELECT * FROM `news` ORDER BY `id` ASC, `title` DESC', $this->getDAL()->from('news')->orderBy('id ASC, title DESC')->buildSQL());

		/* GROUP BY */
		$this->assertEquals('SELECT * FROM `news` GROUP BY COUNT(*)', $this->getDAL()->from('news')->groupBy('COUNT(*)')->buildSQL());
		$this->assertEquals('SELECT * FROM `news` GROUP BY `id`', $this->getDAL()->from('news')->groupBy('id')->buildSQL());
		$this->assertEquals('SELECT * FROM `news` GROUP BY `news`.`id`', $this->getDAL()->from('news')->groupBy('news.id')->buildSQL());
		$this->assertEquals('SELECT * FROM `news` GROUP BY `id`, `title`', $this->getDAL()->from('news')->groupBy('id, title')->buildSQL());

		/* WHERE */
		$this->assertEquals('SELECT * FROM `news` WHERE COUNT(*) NOT IN (SELECT * FROM tests)', $this->getDAL()->from('news')->where('COUNT(*) NOT IN (SELECT * FROM tests)')->buildSQL());
		$this->assertEquals('SELECT * FROM `news` WHERE `id`=4', $this->getDAL()->from('news')->where('id=4')->buildSQL());
		$this->assertEquals('SELECT * FROM `news` WHERE `id`=?', $this->getDAL()->from('news')->where('id=?', 4)->buildSQL());
		$this->assertEquals('SELECT * FROM `news` WHERE `id`=?', $this->getDAL()->from('news')->where('id', 4)->buildSQL());
		$this->assertEquals('SELECT * FROM `news` WHERE `id`=?', $this->getDAL()->from('news')->where(['id' => 4])->buildSQL());
		$this->assertEquals('SELECT * FROM `news` WHERE `id`=?', $this->getDAL()->from('news')->where(['id=?' => 4])->buildSQL());

		$dal = $this->getDAL()->from('news')->where([
			'id=?' => 4,
			'news.title LIKE ?' => '%test%'
		]);
		$this->assertEquals('SELECT * FROM `news` WHERE `id`=? AND `news`.`title` LIKE ?', $dal->buildSQL());
		$this->assertEquals([4, '%test%'], $dal->getParameters());

		$dal = $this->getDAL()->from('news')->where([
			'or' => [
				'id=?' => 4,
				'and' => [
					'news.title LIKE ?' => '%test%',
					'news.content LIKE ?' => '%bla%'
				]
			]
		]);
		$this->assertEquals('SELECT * FROM `news` WHERE `id`=? OR (`news`.`title` LIKE ? AND `news`.`content` LIKE ?)', $dal->buildSQL());
		$this->assertEquals([4, '%test%', '%bla%'], $dal->getParameters());

		#make sure conditions do not override
		$dal = $this->getDAL()->from('news')
			->where('news.title LIKE ?', '%abc%')
			->where('news.title LIKE ?', '%cba%');
		$this->assertEquals('SELECT * FROM `news` WHERE `news`.`title` LIKE ? AND `news`.`title` LIKE ?', $dal->buildSQL());
		$this->assertEquals(['%abc%', '%cba%'], $dal->getParameters());

		#make sure it add brackets to multiple subconditions like OR or AND
		$dal = $this->getDAL()->from('news')->where(['news.title LIKE ?' => '%test%'])
			->where([
				'or' => [
					'id=?' => 4,
					'and' => [
						'news.title LIKE ?' => '%test%',
						'news.content LIKE ?' => '%bla%'
					]
				]
			]);
		$this->assertEquals('SELECT * FROM `news` WHERE `news`.`title` LIKE ? AND (`id`=? OR (`news`.`title` LIKE ? AND `news`.`content` LIKE ?))', $dal->buildSQL());
		$this->assertEquals(['%test%', 4, '%test%', '%bla%'], $dal->getParameters());

		$dal = $this->getDAL()->from('news')->where([
			['news.title LIKE ?' => '%test%'],
			['news.title LIKE ?' => '%bla%'],
		]);
		$this->assertEquals('SELECT * FROM `news` WHERE `news`.`title` LIKE ? AND `news`.`title` LIKE ?', $dal->buildSQL());
		$this->assertEquals(['%test%', '%bla%'], $dal->getParameters());

		/* LEFTJOIN, #rightjoin, #innerjoin */
		$this->assertEquals('SELECT * FROM `news` `n` LEFT JOIN `category` ON COUNT(*)=3', $this->getDAL()->from('news n')->leftjoin('category', 'COUNT(*)=3')->buildSQL());

		$dal = $this->getDAL()->from('news n')->leftjoin('category', ['COUNT(*)=?' => 3]);
		$this->assertEquals('SELECT * FROM `news` `n` LEFT JOIN `category` ON COUNT(*)=?', $dal->buildSQL());
		$this->assertEquals([3], $dal->getParameters());

		$dal = $this->getDAL()->from('news n')->leftjoin('category', ['COUNT(*)=?' => 3]);
		$this->assertEquals('SELECT * FROM `news` `n` LEFT JOIN `category` ON COUNT(*)=?', $dal->buildSQL());
		$this->assertEquals([3], $dal->getParameters());

		$this->assertEquals('SELECT * FROM `news` `n` LEFT JOIN `category` ON `category`.`id`=`news`.`id`', $this->getDAL()->from('news n')->leftjoin('category', 'category.id=news.id')->buildSQL());
		$this->assertEquals('SELECT * FROM `news` `n` LEFT JOIN `category` `c` ON `c`.`id`=`news`.`id`', $this->getDAL()->from('news n')->leftjoin('category c', 'c.id=news.id')->buildSQL());
		$this->assertEquals('SELECT * FROM `news` `n` LEFT JOIN `category` `c` ON `c`.`id`=`news`.`id`', $this->getDAL()->from('news n')->leftjoin('category c', ['c.id=news.id'])->buildSQL());

		$this->assertEquals('SELECT * FROM `news` `n`', $this->getDAL()->from('news n')->leftjoin('category c', ['c.id=news.id'])->removeJointure('c')->buildSQL());

		$this->assertEquals('SELECT * FROM `news` `n` LEFT JOIN `category` `c` ON `c`.`id`=`news`.`id` LEFT JOIN `tag` `t` ON `t`.`id`=`news`.`id` AND `t`.`id`=`news`.`id`', $this->getDAL()->from('news n')->leftjoin([
			'category c' => 'c.id=news.id',
			'tag t' => [
				't.id=news.id',
				't.id=news.id',
			]
		])->buildSQL());

		/* NEXT, QUERY AND GET */
		$query = $this->getDAL()->query('SELECT * FROM news');
		$this->assertInstanceOf('Asgard\Db\Query', $query);
		$data = $query->all();
		$this->assertCount(3, $data);

		$dal = $this->getDAL()->from('news');
		$_data = [];
		while($n = $dal->next())
			$_data[] = $n;
		$this->assertEquals($data, $_data);

		/* RESET */
		$dal = $this->getDAL()->from('news')
			->select('test')
			->leftjoin('category1')
			->rightjoin('category2')
			->innerjoin('category3')
			->where('1=1')
			->orderBY('id ASC')
			->groupBY('id');
		$this->assertEquals(
			'SELECT `test` FROM `news` LEFT JOIN `category1` RIGHT JOIN `category2` INNER JOIN `category3` WHERE 1=1 GROUP BY `news`.`id` ORDER BY `news`.`id` ASC',
			$dal->buildSQL()
		);
		$this->assertEquals('SELECT * FROM `ble`', $dal->reset()->addFrom('ble')->buildSQL());

		/* FIRST */
		$this->assertEquals(1, $this->getDAL()->from('news')->first()['id']);

		/* PAGINATOR */
		$dal = $this->getDAL()->from('news');
		$dal->paginate(3, 10);
		$this->assertEquals('SELECT * FROM `news` LIMIT 20, 10', $dal->buildSQL());
		$this->assertInstanceOf('Asgard\Common\Paginator', $dal->getPaginator());

		/* COUNT */
		$this->assertEquals(3, $this->getDAL()->from('news')->count());
		$res = $this->getDAL()->from('news')->count('*', 'category_id');
		$this->assertEquals([1=>'2', 2=>'1'], $res);

		/* MIN, MAX, AVG, SUM */
		$this->assertEquals(1, $this->getDAL()->from('news')->min('score'));
		$res = $this->getDAL()->from('news')->min('score', 'category_id');
		$this->assertEquals([1=>'2', 2=>'1'], $res);

		/* UPDATE */
		$dal = $this->getDAL()->from('news n')->where('id>?', 3)->orderBy('id ASC')->limit(5);
		$sql = $dal->buildUpdateSQL([
			'title' => 'bla',
			'content' => 'ble',
		]);
		#$this->assertEquals('UPDATE `news` `n` SET `title`=?, `content`=? WHERE `id`>? ORDER BY `id` ASC LIMIT 5', $sql);
		$this->assertEquals('UPDATE `news` SET `title`=?, `content`=? WHERE EXISTS (SELECT 1 FROM `news` `thisIsAUniqueAlias` WHERE `id`>? AND (thisIsAUniqueAlias.id IS NULL AND news.id IS NULL OR thisIsAUniqueAlias.id = news.id) AND (thisIsAUniqueAlias.title IS NULL AND news.title IS NULL OR thisIsAUniqueAlias.title = news.title) AND (thisIsAUniqueAlias.content IS NULL AND news.content IS NULL OR thisIsAUniqueAlias.content = news.content) AND (thisIsAUniqueAlias.category_id IS NULL AND news.category_id IS NULL OR thisIsAUniqueAlias.category_id = news.category_id) AND (thisIsAUniqueAlias.author_id IS NULL AND news.author_id IS NULL OR thisIsAUniqueAlias.author_id = news.author_id) AND (thisIsAUniqueAlias.score IS NULL AND news.score IS NULL OR thisIsAUniqueAlias.score = news.score) ORDER BY `id` ASC LIMIT 5)', $sql);
		$this->assertEquals(['bla', 'ble', 3], $dal->getParameters());
		$this->assertEquals(0, $dal->update(['title' => 'bla', 'content' => 'ble']));

		#not possible with sqlite
		// $dal = $this->getDAL()->from('news n, category c')->where('id>?', 3)->orderBy('id ASC')->limit(5);
		// $sql = $dal->buildUpdateSQL([
		// 	'title' => 'bla',
		// 	'content' => 'ble',
		// ]);
		// #$this->assertEquals('UPDATE `news` `n`, `category` `c` SET `title`=?, `content`=? WHERE `id`>? ORDER BY `id` ASC LIMIT 5', $sql);
		// $this->assertEquals('UPDATE `news`, `category` SET `title`=?, `content`=? WHERE `id`>?', $sql);
		// $this->assertEquals(['bla', 'ble', 3], $dal->getParameters());

		/* DELETE */
		$dal = $this->getDAL()->from('news n')->where('id>?', 3)->orderBy('id ASC')->limit(5);
		#$this->assertEquals('DELETE FROM `news` WHERE `id`>? ORDER BY `id` ASC LIMIT 5', $dal->buildDeleteSQL());
		$this->assertEquals('DELETE FROM `news` WHERE EXISTS (SELECT 1 FROM `news` `thisIsAUniqueAlias` WHERE `id`>? AND (thisIsAUniqueAlias.id IS NULL AND news.id IS NULL OR thisIsAUniqueAlias.id = news.id) AND (thisIsAUniqueAlias.title IS NULL AND news.title IS NULL OR thisIsAUniqueAlias.title = news.title) AND (thisIsAUniqueAlias.content IS NULL AND news.content IS NULL OR thisIsAUniqueAlias.content = news.content) AND (thisIsAUniqueAlias.category_id IS NULL AND news.category_id IS NULL OR thisIsAUniqueAlias.category_id = news.category_id) AND (thisIsAUniqueAlias.author_id IS NULL AND news.author_id IS NULL OR thisIsAUniqueAlias.author_id = news.author_id) AND (thisIsAUniqueAlias.score IS NULL AND news.score IS NULL OR thisIsAUniqueAlias.score = news.score) ORDER BY `id` ASC LIMIT 5)', $dal->buildDeleteSQL());
		$this->assertEquals([3], $dal->getParameters());
		$this->assertEquals(0, $dal->delete());

		#not possible with sqlite
		// $dal = $this->getDAL()->from('news n, category c')->where('id>?', 3)->orderBy('id ASC')->limit(5);
		// #$this->assertEquals('DELETE FROM `news`, `category` WHERE `id`>? ORDER BY `id` ASC LIMIT 5', $dal->buildDeleteSQL());
		// $this->assertEquals('DELETE FROM `news`, `category` WHERE `id`>?', $dal->buildDeleteSQL());
		// $this->assertEquals([3], $dal->getParameters());

		#$dal = $this->getDAL()->from('news n')->leftJoin('category')->where('id>?', 3)->orderBy('id ASC')->limit(5);
		$dal = $this->getDAL()->from('news')->where('id>?', 3);
		#$this->assertEquals('DELETE `n` FROM `news` `n` LEFT JOIN `category` WHERE `n`.`id`>? ORDER BY `n`.`id` ASC LIMIT 5', $dal->buildDeleteSQL(['n']));
		$this->assertEquals('DELETE FROM `news` WHERE `id`>?', $dal->buildDeleteSQL(['n']));
		$this->assertEquals([3], $dal->getParameters());

		/* INSERT */
		$dal = $this->getDAL()->into('news');
		$this->assertEquals('INSERT INTO `news` (`id`) VALUES (?)', $dal->buildInsertSQL(['id'=>5]));
		$this->assertEquals([5], $dal->getParameters());
		$this->assertEquals(5, $dal->insert(['id'=>5]));

		/* EXCEPTION */
		$this->setExpectedException('Exception');
		$this->getDAL()->get();
	}
}