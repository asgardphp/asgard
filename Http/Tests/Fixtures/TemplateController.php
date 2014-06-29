<?php
namespace Asgard\Http\Tests\Fixtures;

class TemplateController extends \Asgard\Http\Controller {
	public function homeAction($request) {
		return new Templates\Template('sample', ['content'=>'home!']);
	}

	public function home2Action($request) {
		$this->content = 'home!';
		$this->view = 'sample';
	}
}