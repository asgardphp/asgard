<?php
namespace Asgard\Tester\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run tester command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class RunCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'tester:run';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Run the tester';
	/**
	 * Http kernel dependency.
	 * @var \Asgard\Http\HttpKernelInterface
	 */
	protected $httpKernel;
	/**
	 * Resolver dependency.
	 * @var \Asgard\Http\ResolverInterface
	 */
	protected $resolver;
	/**
	 * Db dependency.
	 * @var \Asgard\Db\DBInterface
	 */
	protected $db;
	/**
	 * Migration manager dependency.
	 * @var \Asgard\Migration\MigrationManagerInterface
	 */
	protected $mm;

	/**
	 * Constructor.
	 * @param \Asgard\Http\HttpKernelInterface            $httpKernel
	 * @param \Asgard\Http\ResolverInterface              $resolver
	 * @param \Asgard\Db\DBInterface                      $db
	 * @param \Asgard\Migration\MigrationManagerInterface $mm
	 */
	public function __construct(\Asgard\Http\HttpKernel $httpKernel, \Asgard\Http\ResolverInterface $resolver, \Asgard\Db\DBInterface $db=null, \Asgard\Migration\MigrationManagerInterface $mm=null) {
		$this->httpKernel = $httpKernel;
		$this->resolver = $resolver;
		$this->db = $db;
		$this->mm = $mm;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$configClass = '\\'.$this->input->getOption('config');
		$output = $this->input->getOption('output');

		$config = new $configClass();

		$browser = new \Asgard\Tester\Browser($this->httpKernel);

		$results = new \Asgard\Tester\Results;

		if(!$config->coverage)
			$config->coverage = [realpath('app')];

		$crawler = new \Asgard\Tester\Crawler($this, $config, $this->resolver, $results, $browser);

		$request = new \Asgard\Http\Request;
		$request->url->setUrl('');
		$requests = [$request];
		$crawler->addRequests($requests);

		$crawler->start();

		if($output) {
			$name = $orig = $output;
			$i = 1;
			while(file_exists('tests/'.$name.'.php'))
				$name = $orig.'_'.$i++;

			#Fixtures
			if($this->db) {
				$fixturesFile = $name.'_fixtures.php';
				$fixtures = new \Asgard\Tester\Fixtures($this->db, $this->mm);
				$fixtures->generate('tests/'.$fixturesFile);
			}
			else
				$fixturesFile = null;

			#Tests
			$results->generate($name, $fixturesFile);

			$this->info('Test file "tests/'.$name.'.php" was generated with success.');
		}
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getOptions() {
		return [
			['config', null, InputOption::VALUE_REQUIRED, 'Configuration class.', 'Asgard\Tester\Config'],
			['output', null, InputOption::VALUE_OPTIONAL, 'Output tests. Test class name is optional.', null],
		];
	}
}