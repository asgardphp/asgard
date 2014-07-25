<?php
namespace Asgard\Core\Tests;

class BundleTest {
	// public function testAddBundlesDirs() {
	// 	$container = new \Asgard\Container\Container;
	// 	$bm = new \Asgard\Core\BundlesManager($container);
	// 	$bm->addBundlesDirs(__DIR__.'/Fixtures/bundles/');
	// 	$this->assertInstanceOf('Asgard\Core\Tests\Fixtures\Bundles\Bundle\Bundle', $bm->getBundles()[0]);
	// }

	// public function testAddBundles() {
	// 	$container = new \Asgard\Container\Container;
	// 	$bm = new \Asgard\Core\BundlesManager($container);
	// 	$bm->addBundles(new \Asgard\Core\Tests\Fixtures\Bundles\Bundle\Bundle);
	// 	$this->assertInstanceOf('Asgard\Core\Tests\Fixtures\Bundles\Bundle\Bundle', $bm->getBundles()[0]);
	// }

	// public function testAddBundlesWithoutClass() {
	// 	$container = new \Asgard\Container\Container;
	// 	$bm = new \Asgard\Core\BundlesManager($container);
	// 	$bm->addBundles(__DIR__.'/Fixtures/bundles');
	// 	$this->assertInstanceOf('Asgard\Core\BundleLoader', $bm->getBundles()[0]);
	// }

	// public function testGetBundlesPaths() {
	// 	$container = new \Asgard\Container\Container;
	// 	$bm = new \Asgard\Core\BundlesManager($container);
	// 	$bm->addBundles(new \Asgard\Core\Tests\Fixtures\Bundles\Bundle\Bundle);
	// 	$this->assertEquals(
	// 		array(
	// 			realpath(__DIR__.'/Fixtures/bundles/bundle')
	// 		),
	// 		$bm->getBundlesPath()
	// 	);
	// }

	// public function testLoadBundles() {
	// 	$bm = new \Asgard\Core\BundlesManager(new \Asgard\Container\Container);
	// 	$bundle = $this->getMock('Asgard\Core\BundleLoader', array('load', 'run'));
	// 	$bundle->expects($this->once())->method('load');
	// 	$bundle->expects($this->once())->method('run');
	// 	$bm->addBundle($bundle);
	// 	$bm->loadBundles();
	// }

	// public function testBundleLoad() {
	// 	$autoloader = $this->getMock('Asgard\Autoloader\Autoloader', array('preloadDir'));
	// 	$autoloader->expects($this->once())->method('preloadDir')->with(realpath(__DIR__.'/Fixtures/bundles/bundle'));
	// 	$container = new \Asgard\Container\Container(array(
	// 		'autoloader' => $autoloader
	// 	));
	// 	$bm = new \Asgard\Core\BundlesManager($container);
	// 	$bundle = new \Asgard\Core\Tests\Fixtures\Bundles\Bundle\Bundle;
	// 	$bundle->setContainer($container);
	// 	$bundle->load($bm);
	// }

	// public function testBundleRun() {
	// 	$translator = $this->getMock('StdClass', array('addLocales', 'fetchLocalesFromDir'));
	// 	$translator->expects($this->once())->method('fetchLocalesFromDir')->with(realpath(__DIR__.'/Fixtures/bundles/bundle/locales'));
	// 	$translator->expects($this->once())->method('addLocales')->with(null);
	// 	$hook = $this->getMock('StdClass', array('hooks'));
	// 	$hook->expects($this->once())->method('hooks')->with([]);
	// 	$resolver = $this->getMock('StdClass', array('addRoutes'));
	// 	$resolver->expects($this->once())->method('addRoutes')->with([]);
	// 	$clirouter = $this->getMock('StdClass', array('addRoutes'));
	// 	$clirouter->expects($this->once())->method('addRoutes')->with([]);
	// 	$container = new \Asgard\Container\Container(array(
	// 		'translator' => $translator,
	// 		'hook' => $hook,
	// 		'resolver' => $resolver,
	// 		'clirouter' => $clirouter,
	// 		'cache' => new \Asgard\Cache\NullCache,
	// 	));
	// 	$bm = new \Asgard\Core\BundlesManager($container);
	// 	$bundle = new \Asgard\Core\Tests\Fixtures\Bundles\Bundle\Bundle;
	// 	$bundle->setContainer($container);
	// 	$bundle->setConsole(true);
	// 	$bundle->run();
	// }
}
