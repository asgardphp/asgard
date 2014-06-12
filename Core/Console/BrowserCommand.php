<?php
namespace Asgard\Core\Console;

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
		
		$headers = $input->getOption('h') ? json_decode($input->getOption('h')):[];
		$post = $input->getOption('p') ? json_decode($input->getOption('p')):[];
		$session = $input->getOption('ss') ? json_decode($input->getOption('ss')):[];
		$server = $input->getOption('sr') ? json_decode($input->getOption('sr')):[];
		$cookies = $input->getOption('c') ? json_decode($input->getOption('c')):[];
		$body = $input->getOption('b');
		$files = $input->getOption('f') ? json_decode($input->getOption('f')):[];
		if($files) {
			$files = json_decode($files);
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
		return [
			['showAll', null, InputOption::VALUE_NONE, 'Show the whole response'],
			['showSession', null, InputOption::VALUE_NONE, 'Show response session'],
			['showCookies', null, InputOption::VALUE_NONE, 'Show response cookies'],
			['showHeaders', null, InputOption::VALUE_NONE, 'Show response headers'],
			['showCode', null, InputOption::VALUE_NONE, 'Show response code'],
			['showContent', null, InputOption::VALUE_NONE, 'Show response content'],
			['h', null, InputOption::VALUE_OPTIONAL, 'Headers'],
			['p', null, InputOption::VALUE_OPTIONAL, 'Post data'],
			['f', null, InputOption::VALUE_OPTIONAL, 'Files'],
			['ss', null, InputOption::VALUE_OPTIONAL, 'Session data'],
			['sr', null, InputOption::VALUE_OPTIONAL, 'Server data'],
			['c', null, InputOption::VALUE_OPTIONAL, 'Cookies'],
			['b', null, InputOption::VALUE_OPTIONAL, 'Body'],
		];
	}

	protected function getArguments() {
		return [
			['method', InputArgument::REQUIRED, 'The HTTP method'],
			['url', InputArgument::REQUIRED, 'The HTTP url'],
		];
	}
}