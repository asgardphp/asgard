<?php
namespace Tester;

class Browser extends \Asgard\Http\Browser\Browser {
	public function request(\Asgard\Http\Request $request) {
		$c = new Bag;
		$c->setAll($this->cookies->all());
		$request->cookie = $c;
		$s = new Bag;
		$s->setAll($this->session->all());
		$request->session = $s;

		$r2 = clone $request;

		$res = $this->httpKernel->process($r2, $this->catchException);

		$request->cookie->accessed = $r2->cookie->accessed;
		$request->session->accessed = $r2->session->accessed;

		$this->last = $res;
		$this->cookies = $r2->cookie;
		$this->session = $r2->session;

		return $res;
	}
}