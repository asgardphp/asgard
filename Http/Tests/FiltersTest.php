<?php
namespace Asgard\Hook\Tests;

use \Asgard\Http\Controller;
use \Asgard\Http\ControllerRoute;
use \Asgard\Http\Resolver;
use \Asgard\Http\Request;

class FiltersTest extends \PHPUnit_Framework_TestCase {
	public function testLayout() {
		$app = new \Asgard\Container\Container;
		$app['hooks'] = new \Asgard\Hook\HooksManager($app);
		$controller = new \Asgard\Http\Tests\Fixtures\Controllers\FooController();
		$controller->addFilter(new \Asgard\Http\Filters\PageLayout(function($content) { return '<h1>'.$content.'</h1>'; }));
		$res = $controller->run('page', $app);

		$this->assertEquals('<h1>hello!</h1>', $res->content);
	}

	public function testJson() {
		$app = new \Asgard\Container\Container;
		$app['hooks'] = new \Asgard\Hook\HooksManager($app);
		$app['cache'] = new \Asgard\Cache\NullCache();
		$app['config'] = new \Asgard\Config\Config();
		$app['entitiesmanager'] = new \Asgard\Entity\EntitiesManager($app);
		\Asgard\Entity\Entity::setApp($app);

		$controller = new \Asgard\Http\Tests\Fixtures\Controllers\FooController();
		$controller->addFilter(new \Asgard\Http\Filters\JSONEntities());
		$res = $controller->run('json', $app);

		$this->assertEquals('[{"title":"hello","content":"world"},{"title":"welcome","content":"home"}]', $res->content);
	}
}