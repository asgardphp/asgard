<?php
namespace Asgard\Translation;

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
	 * @param string $dir
	 */
	public function __construct($httpKernel, $resolver, $db, $mm) {
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

		$browser = new Browser($this->httpKernel);

		$results = new Results;

		if(!$config->coverage)
			$config->coverage = [realpath('app')];

		$crawler = new Crawler($this, $config, $this->resolver, $results, $browser);

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
			$fixturesFile = $name.'_fixtures.php';

			#Fixtures
			$fixtures = new Fixtures($this->db, $this->mm);
			$fixtures->generate('tests/'.$fixturesFile);

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