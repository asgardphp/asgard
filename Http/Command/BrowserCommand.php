<?php
namespace Asgard\Http\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Browser command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class BrowserCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'browser';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Execute an HTTP request';
	/**
	 * HTTPKernel dependency.
	 * @var \Asgard\Http\HttpKernelInterface
	 */
	protected $httpKernel;

	/**
	 * Constructor.
	 * @param \Asgard\Http\HttpKernelInterface $httpKernel
	 */
	public function __construct(\Asgard\Http\HttpKernelInterface $httpKernel) {
		$this->httpKernel = $httpKernel;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$method = $this->input->getArgument('method');
		$url = $this->input->getArgument('url');

		$headers = $this->input->getOption('h') ? json_decode($this->input->getOption('h'), true):[];
		$post = $this->input->getOption('p') ? json_decode($this->input->getOption('p'), true):[];
		$session = $this->input->getOption('ss') ? json_decode($this->input->getOption('ss'), true):[];
		$server = $this->input->getOption('sr') ? json_decode($this->input->getOption('sr'), true):[];
		$cookies = $this->input->getOption('c') ? json_decode($this->input->getOption('c'), true):[];
		$body = $this->input->getOption('b');
		$files = $this->input->getOption('f') ? json_decode($this->input->getOption('f'), true):[];

		$browser = new \Asgard\Http\Browser\Browser($this->httpKernel, $this->getContainer());
		$browser->getCookies()->setAll($cookies);
		$browser->getSession()->setAll($session);
		$response = $browser->req($url, $method, $post, $files, $body, $headers, $server);

		$showContent = $this->input->getOption('showContent');
		$showCode = $this->input->getOption('showCode');
		$showAll = $this->input->getOption('showAll');
		$showSession = $this->input->getOption('showSession');
		$showCookies = $this->input->getOption('showCookies');
		$showHeaders = $this->input->getOption('showHeaders');
		if(!$showCode && !$showAll && !$showSession && !$showCookies && !$showHeaders)
			$showContent = true;

		if($showAll || $showCode)
			$this->output->writeln('Code: '.($response->isOK() ? '<info>':'<error>').$response->getCode().($response->isOK() ? '</info>':'</error>'));
		if($showAll || $showContent)
			$this->output->writeln($response->getContent());
		if($showAll || $showSession) {
			$this->output->writeln('Session:');
			$this->output->writeln(json_encode($browser->getSession()->all(), JSON_PRETTY_PRINT));
		}
		if($showAll || $showCookies) {
			$this->output->writeln('Cookies');
			$this->output->writeln(json_encode($browser->getCookies()->all(), JSON_PRETTY_PRINT));
		}
		if($showAll || $showHeaders) {
			$this->output->writeln('Headers');
			$this->output->writeln(json_encode($response->getHeaders(), JSON_PRETTY_PRINT));
		}
	}

	/**
	 * {@inheritDoc}
	 */
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

	/**
	 * {@inheritDoc}
	 */
	protected function getArguments() {
		return [
			['method', InputArgument::REQUIRED, 'The HTTP method'],
			['url', InputArgument::REQUIRED, 'The HTTP url'],
		];
	}
}