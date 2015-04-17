<?php
namespace Asgard\Generator;

interface GeneratorEngineInterface {
	public function generate(array $bundles, $root);

	public function addGenerator(AbstractGenerator $generator);

	public function processFile($src, $dst, $vars);

	public function setOverrideFiles($overrideFiles);
}