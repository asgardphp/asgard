<?php
namespace Asgard\Tester\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Tester curl command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class CoverageCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'tester:coverage';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Convert a curl query to an Asgard request.';
	/**
	 * Config.
	 * @var array
	 */
	protected $config;
	/**
	 * Displayed elements.
	 * @var array
	 */
	protected $elements = [];

	/**
	 * Constructor.
	 * @param array $config
	 */
	public function __construct(array $config) {
		if(!isset($config['include']))
			$config['include'] = [];
		if(!isset($config['exclude']))
			$config['exclude'] = [];
		$this->config = $config;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$file = $input->getArgument('file');

		$coverage = require $file;
		$report = $coverage->getReport();

		$this->help();

		while(true) {
			$dialog = $this->getHelperSet()->get('question');
			$cmd = $dialog->ask(
				$input,
				$output,
				new \Symfony\Component\Console\Question\Question('>', 'help')
			);
			$e = explode(' ', $cmd);
			$cmd = $e[0];
			$args = array_slice($e, 1);
			switch($cmd) {
				case 'coverage':
					$this->coverage($report);
					break;
				case 'quit':
					return;
				case 'show':
					$this->show($args[0]);
					break;
				case 'help':
				default:
					$this->help();
					break;
			}
		}
	}

	protected function showCoverage($fileNode, $start=null, $end=null) {
		$data = $fileNode->getCoverageData();
		$lines = explode("\n", file_get_contents($fileNode->getPath()));
		foreach($lines as $k=>$line) {
			if($start !== null && $k < $start)
				continue;
			if($end !== null && $k > $end)
				break;

			if(!isset($data[$k+1]))
				echo ' ';
			elseif($data[$k+1] === null)
				echo ' ';
			elseif($data[$k+1])
				$this->output->write('+');
			else
				$this->output->write('-');
			$this->output->writeln($line);
		}
	}

	protected function show($i) {
		if(!isset($this->elements[$i]))
			return;
		$element = $this->elements[$i];

		if(is_string($element)) {
			$c = file_get_contents($element);
			$c = implode("\n", array_map(function($a){return '-'.$a;}, explode("\n", $c)));
			$this->output->writeln($c);
		}
		elseif($element instanceof \PHP_CodeCoverage_Report_Node_File) {
			$this->showCoverage($element);
		}
		elseif(is_array($element)) {
			$class = $element['class'];
			$reflection = new \ReflectionClass($class);
			$file = $reflection->getFileName();

			if(isset($element['className'])) {
				$start = $reflection->getStartLine();
				$end = $reflection->getEndLine();
			}
			elseif(isset($element['methodName'])) {
				$start = $element['startLine'];
				$end = $element['endLine'];
			}

			if(isset($start)) {
				$this->showCoverage($element['file'], $start-1, $end);
			}
		}
	}

	protected function help() {
		$this->output->writeln("Command list:
coverage: show coverage percentages
show [id]: show covered source code
help: this
quit: quit");
	}

	protected function coverage($report) {
		$files = [];
		foreach($report as $node) {
			if($node instanceof \PHP_CodeCoverage_Report_Node_File) {
				$path = $this->realpath($node->getPath());
				$f = new \Asgard\File\File($path);
				$found = false;
				foreach($this->config['include'] as $d) {
					if($f->isIn($d) || $f->isAt($d)) {
						$found = true;
						break;
					}
				}
				if(!$found)
					continue;
				foreach($this->config['exclude'] as $d) {
					if($f->isIn($d) || $f->isAt($d))
						continue 2;
				}

				$files[$path] = $node;
			}
		}
		
		$this->processFiles($this->config['include'], function($file) use(&$files) {
			$file = $this->realpath($file);

			$f = new \Asgard\File\File($file);
			$found = false;
			foreach($this->config['include'] as $d) {
				if($f->isIn($d) || $f->isAt($d)) {
					$found = true;
					break;
				}
			}
			if(!$found)
				return;
			foreach($this->config['exclude'] as $d) {
				if($f->isIn($d) || $f->isAt($d))
					return;
			}

			if(array_key_exists($file, $files))
				return;
			$files[] = $file;
		});
		
		usort($files, function($a, $b) {
			if(is_string($a))
				return -1;
			if(is_string($b))
				return 1;

			$percentA = \PHP_CodeCoverage_Util::percent(
				$a->getNumExecutedLines(),
				$a->getNumExecutableLines(),
				false
			);
			$percentB = \PHP_CodeCoverage_Util::percent(
				$b->getNumExecutedLines(),
				$b->getNumExecutableLines(),
				false
			);
			return $percentA > $percentB;
		});

		$this->display($files);
	}

	protected function realpath($path) {
		$path = realpath($path);
		$path = str_replace('\\', '/', $path);
		return $path;
	}

	protected function display($files) {
		$this->elements = [];
		$i = 0;

		foreach($files as $file) {
			if(is_string($file)) {
				$this->output->writeln('['.$i.']'.$file.': 0%');
				$this->elements[$i++] = $file;
				continue;
			}

			$percent = \PHP_CodeCoverage_Util::percent(
				$file->getNumExecutedLines(),
				$file->getNumExecutableLines(),
				false
			);
			if($percent === 100)
				continue;
			$this->output->writeln('['.$i.']'.$this->realpath($file->getPath()).': '.round($percent, 2).'%');
			$this->elements[$i++] = $file;

			$classes = $file->getClasses();
			uasort($classes, function($a, $b) {
				$percentA = \PHP_CodeCoverage_Util::percent(
					$a['executedLines'],
					$a['executableLines'],
					false
				);
				$percentB = \PHP_CodeCoverage_Util::percent(
					$b['executedLines'],
					$b['executableLines'],
					false
				);
				return $percentA > $percentB;
			});
			foreach($classes as $className=>$class) {
				$_class = (isset($class['package']['namespace']) ? $class['package']['namespace']:'').'\\'.$className;

				$percent = \PHP_CodeCoverage_Util::percent(
					$class['executedLines'],
					$class['executableLines'],
					false
				);
				if($percent === 100)
					continue;
				$this->output->writeln("\t\t".'['.$i.']'.$className.': '.round($percent, 2).'%');
				$class['class'] = $_class;
				$class['file'] = $file;
				$this->elements[$i++] = $class;

				$methods = $class['methods'];
				uasort($methods, function($a, $b) {
					$percentA = \PHP_CodeCoverage_Util::percent(
						$a['executedLines'],
						$a['executableLines'],
						false
					);
					$percentB = \PHP_CodeCoverage_Util::percent(
						$b['executedLines'],
						$b['executableLines'],
						false
					);
					return $percentA > $percentB;
				});
				foreach($methods as $methodName=>$method) {
					$percent = \PHP_CodeCoverage_Util::percent(
						$method['executedLines'],
						$method['executableLines'],
						false
					);
					if($percent === 100)
						continue;
					$this->output->writeln("\t\t\t".'['.$i.']'.$methodName.': '.round($percent, 2).'%');
					$method['class'] = $_class;
					$method['file'] = $file;
					$this->elements[$i++] = $method;
				}
			}
		}
	}

	protected function processFiles($files, $cb) {
		foreach($files as $file) {
			if(is_dir($file))
				$this->processFiles(glob($file.'/*'), $cb);
			else
				$cb($file);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getArguments() {
		return [
			['file', InputArgument::REQUIRED, 'Coverage file.'],
		];
	}
}