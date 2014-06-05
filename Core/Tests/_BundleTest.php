<?php
namespace Asgard\Core\Tests;

class BundleTest {
	// public function testAddBundlesDirs() {
	// 	$app = new \Asgard\Core\App;
	// 	$bm = new \Asgard\Core\BundlesManager($app);
	// 	$bm->addBundlesDirs(__DIR__.'/Fixtures/bundles/');
	// 	$this->assertInstanceOf('Asgard\Core\Tests\Fixtures\Bundles\Bundle\Bundle', $bm->getBundles()[0]);
	// }

	// public function testAddBundles() {
	// 	$app = new \Asgard\Core\App;
	// 	$bm = new \Asgard\Core\BundlesManager($app);
	// 	$bm->addBundles(new \Asgard\Core\Tests\Fixtures\Bundles\Bundle\Bundle);
	// 	$this->assertInstanceOf('Asgard\Core\Tests\Fixtures\Bundles\Bundle\Bundle', $bm->getBundles()[0]);
	// }

	// public function testAddBundlesWithoutClass() {
	// 	$app = new \Asgard\Core\App;
	// 	$bm = new \Asgard\Core\BundlesManager($app);
	// 	$bm->addBundles(__DIR__.'/Fixtures/bundles');
	// 	$this->assertInstanceOf('Asgard\Core\BundleLoader', $bm->getBundles()[0]);
	// }

	// public function testGetBundlesPaths() {
	// 	$app = new \Asgard\Core\App;
	// 	$bm = new \Asgard\Core\BundlesManager($app);
	// 	$bm->addBundles(new \Asgard\Core\Tests\Fixtures\Bundles\Bundle\Bundle);
	// 	$this->assertEquals(
	// 		array(
	// 			realpath(__DIR__.'/Fixtures/bundles/bundle')
	// 		),
	// 		$bm->getBundlesPath()
	// 	);
	// }

	// public function testLoadBundles() {
	// 	$bm = new \Asgard\Core\BundlesManager(new \Asgard\Core\App);
	// 	$bundle = $this->getMock('Asgard\Core\BundleLoader', array('load', 'run'));
	// 	$bundle->expects($this->once())->method('load');
	// 	$bundle->expects($this->once())->method('run');
	// 	$bm->addBundle($bundle);
	// 	$bm->loadBundles();
	// }

	// public function testBundleLoad() {
	// 	$autoloader = $this->getMock('Asgard\Core\Autoloader', array('preloadDir'));
	// 	$autoloader->expects($this->once())->method('preloadDir')->with(realpath(__DIR__.'/Fixtures/bundles/bundle'));
	// 	$app = new \Asgard\Core\App(array(
	// 		'autoloader' => $autoloader
	// 	));
	// 	$bm = new \Asgard\Core\BundlesManager($app);
	// 	$bundle = new \Asgard\Core\Tests\Fixtures\Bundles\Bundle\Bundle;
	// 	$bundle->setApp($app);
	// 	$bundle->load($bm);
	// }

	// public function testBundleRun() {
	// 	$translator = $this->getMock('StdClass', array('addLocales', 'fetchLocalesFromDir'));
	// 	$translator->expects($this->once())->method('fetchLocalesFromDir')->with(realpath(__DIR__.'/Fixtures/bundles/bundle/locales'));
	// 	$translator->expects($this->once())->method('addLocales')->with(null);
	// 	$hook = $this->getMock('StdClass', array('hooks'));
	// 	$hook->expects($this->once())->method('hooks')->with(array());
	// 	$resolver = $this->getMock('StdClass', array('addRoutes'));
	// 	$resolver->expects($this->once())->method('addRoutes')->with(array());
	// 	$clirouter = $this->getMock('StdClass', array('addRoutes'));
	// 	$clirouter->expects($this->once())->method('addRoutes')->with(array());
	// 	$app = new \Asgard\Core\App(array(
	// 		'translator' => $translator,
	// 		'hook' => $hook,
	// 		'resolver' => $resolver,
	// 		'clirouter' => $clirouter,
	// 		'cache' => new \Asgard\Cache\NullCache,
	// 	));
	// 	$bm = new \Asgard\Core\BundlesManager($app);
	// 	$bundle = new \Asgard\Core\Tests\Fixtures\Bundles\Bundle\Bundle;
	// 	$bundle->setApp($app);
	// 	$bundle->setConsole(true);
	// 	$bundle->run();
	// }
}
