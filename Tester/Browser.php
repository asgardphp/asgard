<?php
namespace Asgard\Tester;

class Browser extends \Asgard\Http\Browser\Browser {
	public function request(\Asgard\Http\Request $request) {
		$c = new Bag;
		$c->setAll($this->cookies->all());
		$this->cookies = $c;
		$s = new Bag;
		$s->setAll($this->session->all());
		$this->session = $s;

		$res = parent::request($request);

		$request->cookiesAccessed = $this->cookies->getAccessed();
		$request->sessionAccessed = $this->session->getAccessed();

		return $res;
	}
}