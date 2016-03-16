<?php
namespace Asgard\Core\Command;

class ComposerApplication extends \Composer\Console\Application {
	/**
	* {@inheritDoc}
	*/
	protected function getDefaultHelperSet() {
		return new \Symfony\Component\Console\Helper\HelperSet([
			new \Symfony\Component\Console\Helper\FormatterHelper(),
			new \Symfony\Component\Console\Helper\DialogHelper(false),
			new \Symfony\Component\Console\Helper\ProgressHelper(false),
			new \Symfony\Component\Console\Helper\TableHelper(false),
			new \Symfony\Component\Console\Helper\DebugFormatterHelper(),
			new \Symfony\Component\Console\Helper\ProcessHelper(),
			new \Symfony\Component\Console\Helper\QuestionHelper(),
		]);
	}
}