<?php
namespace Asgard\Generator;

abstract class AbstractGenerator {
	protected $engine;

	public function preGenerate(array &$bundle) {
	}

	public function generate(array $bundle, $root, $bundlePath) {
	}

	public function postGenerate(array $bundle, $root, $bundlePath) {
	}

	public function setEngine(GeneratorEngine $engine) {
		$this->engine = $engine;
	}
}