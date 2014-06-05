<?php
namespace Asgard\Http\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class BrowserCommand extends \Asgard\Console\Command {
	protected $name = 'browser';
	protected $description = 'Execute an HTTP request';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$method = $input->getArgument('method');
		$url = $input->getArgument('url');
		
		$headers = $input->getOption('h') ? json_decode($input->getOption('h')):array();
		$post = $input->getOption('p') ? json_decode($input->getOption('p')):array();
		$session = $input->getOption('ss') ? json_decode($input->getOption('ss')):array();
		$server = $input->getOption('sr') ? json_decode($input->getOption('sr')):array();
		$cookies = $input->getOption('c') ? json_decode($input->getOption('c')):array();
		$body = $input->getOption('b');
		$files = $input->getOption('f') ? json_decode($input->getOption('f')):array();
		if($files) {
			$files = json_decode($files);
			#todo
			foreach($files as $k=>$v)
				$files[$k] = new \Asgard\Http\HttpFile($v['path'], $v['name'], $v['size'], $v['error']);
		}

		$browser = new \Asgard\Http\Browser\Browser($this->getAsgard());
		$browser->getCookies()->setAll($cookies);
		$browser->getSession()->setAll($session);
		$response = $browser->req($url, $method, $post, $files, $body, $headers);

		if($input->getOption('showAll') || $input->getOption('showCode'))
			$output->writeln('Code: '.($response->isOK() ? '<info>':'<error>').$response->getCode().($response->isOK() ? '</info>':'</error>'));
		if($input->getOption('showAll') || $input->getOption('showContent'))
			$output->writeln($response->getContent());
		if($input->getOption('showAll') || $input->getOption('showSession')) {
			$output->writeln('Session:');
			$output->writeln(json_encode($browser->getSession()->all(), JSON_PRETTY_PRINT));
		}
		if($input->getOption('showAll') || $input->getOption('showCookies')) {
			$output->writeln('Cookies');
			$output->writeln(json_encode($browser->getCookies()->all(), JSON_PRETTY_PRINT));
		}
		if($input->getOption('showAll') || $input->getOption('showHeaders')) {
			$output->writeln('Headers');
			$output->writeln(json_encode($response->getHeaders(), JSON_PRETTY_PRINT));
		}
	}

	protected function getOptions() {
		return array(
			array('showAll', null, InputOption::VALUE_NONE, 'Show the whole response'),
			array('showSession', null, InputOption::VALUE_NONE, 'Show response session'),
			array('showCookies', null, InputOption::VALUE_NONE, 'Show response cookies'),
			array('showHeaders', null, InputOption::VALUE_NONE, 'Show response headers'),
			array('showCode', null, InputOption::VALUE_NONE, 'Show response code'),
			array('showContent', null, InputOption::VALUE_NONE, 'Show response content'),
			array('h', null, InputOption::VALUE_OPTIONAL, 'Headers'),
			array('p', null, InputOption::VALUE_OPTIONAL, 'Post data'),
			array('f', null, InputOption::VALUE_OPTIONAL, 'Files'),
			array('ss', null, InputOption::VALUE_OPTIONAL, 'Session data'),
			array('sr', null, InputOption::VALUE_OPTIONAL, 'Server data'),
			array('c', null, InputOption::VALUE_OPTIONAL, 'Cookies'),
			array('b', null, InputOption::VALUE_OPTIONAL, 'Body'),
		);
	}

	protected function getArguments() {
		return array(
			array('method', InputArgument::REQUIRED, 'The HTTP method'),
			array('url', InputArgument::REQUIRED, 'The HTTP url'),
		);
	}
}