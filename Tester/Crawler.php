<?php
namespace Asgard\Tester;

class Crawler {
	protected $requests = [];
	protected $done = [];
	protected $console;
	protected $config;
	protected $resolver;
	protected $results;
	protected $browser;
	public $coverage = [];

	public function __construct($console, $config, $resolver, $results, $browser) {
		$this->console = $console;
		$this->config = $config;
		$this->resolver = $resolver;
		$this->results = $results;
		$this->browser = $browser;
	}

	public function getRequestId($request) {
		$post = $request->post->all();
		$file = $request->file->all();
		$cookies = $request->cookie->all();

		ksort($post);
		ksort($file);
		ksort($cookies);

		$url = $request->url->full();
		$body = $request->body;

		$id = sha1(serialize($post).serialize($file).serialize($cookies).$url.$body);

		return $id;
	}

	public function urlMerge($original, $new) {
		if (is_string($original)) {
		    $original = parse_url($original);
		}
		if (is_string($new)) {
		    $new = parse_url($new);
		}
		$qs = null;
		if (!empty($original['query']) && is_string($original['query'])) {
		    parse_str($original['query'], $original['query']);
		}
		if (!empty($new['query']) && is_string($new['query'])) {
		    parse_str($new['query'], $new['query']);
		}
		if (isset($original['query']) || isset($new['query'])) {
		    if (!isset($original['query'])) {
		        $qs = $new['query'];
		    } elseif (!isset($new['query'])) {
		        $qs = $original['query'];
		    } else {
		        $qs = array_merge($original['query'], $new['query']);
		    }
		}
		$result = array_merge($original, $new);
		$result['query'] = $qs;
		foreach ($result as $k => $v) {
		    if ($v === null) {
		        unset($result[$k]);
		    }
		}
		if (!empty($result['query'])) {
		    $result['query'] = http_build_query($result['query']);
		}
		if (!isset($result['path'][0]) || $result['path'][0] != '/') {
		    $result['path'] = "/{$result['path']}";
		}
		return (isset($result['scheme']) ? "{$result['scheme']}://" : '')
		    . (isset($result['user']) ? $result['user']
		        . (isset($result['pass']) ? ":{$result['pass']}" : '').'@' : '')
		    . (isset($result['host']) ? $result['host'] : '')
		    . (isset($result['port']) ? ":{$result['port']}" : '')
		    . (isset($result['path']) ? $result['path'] : '')
		    . (!empty($result['query']) ? "?{$result['query']}" : '')
	    . (isset($result['fragment']) ? "#{$result['fragment']}" : '');
	}

	public function belongsTo($url, $root) {
		$pr = parse_url($root);
		$pu = parse_url($url);
		if($pr['host'] !== $pu['host'])
			return false;
		if(isset($pr['path']) && strpos($pu['path'], $pr['path']) !== 0)
			return false;
		return true;
	}

	public function getBase($current, $doc) {
		$base = $doc->item('base')->attr('href');
		if(!$base) 
			$base = $current;
		else {
			d($current, $base);
			$base = $this->urlMerge($current, $base);
		}

		$base = preg_replace('/[^\/]*$/', '', $base);

		$base = trim($base, '/');
		if($base)
			$base .= '/';

		return $base;
	}

	public function getPath($url) {
		$parts = parse_url($url);
		$path = trim($parts['path'], '/');
		return $path.(isset($parts['query']) ? '?'.$parts['query']:'');
	}

	public function buildUrl($root, $base, $href) {
		$url = $this->urlMerge($base, $href);
		if(!$this->belongsTo($url, $root))
			return false;
		return $this->getPath($url);
	}

	public function prepareRequests($resolver) {
		$requests = [];

		if($this->config->generateStartingRequests) {
			$routes = $resolver->getRoutes();
			foreach($routes as $route) {
				$r = $route->getRoute();
				if(strpos($r, ':') !== false)
					continue;

				$request = new \Asgard\Http\Request;
				$request->url->setUrl($r);

				$method = strtoupper($route->get('method'));
				if(!$method)
					$method = 'GET';
				$request->setMethod($method);

				$this->addRequests($request);
			}
		}

		foreach($this->config->urls as $url) {
			$r = new \Asgard\Http\Request;
			$r->url->setUrl($url);
			$r->setMethod('GET');
			$this->addRequests($r);
		}

		return $requests;
	}

	public function start() {
		$host = $this->config->host;
		$root = $this->config->root;
		$_root = 'http://'.$host.'/'.$root;

		$this->addRequests($this->prepareRequests($this->resolver));

		$coverage = new \PHP_CodeCoverage;

		$this->config->init($this, $this->browser);

		$routes = [];
		foreach($this->resolver->getRoutes() as $r) {
			$routes[] = $r;
		}
		foreach($routes as $k=>$r) {
			if(!$this->isValid($r->getRoute()))
				unset($routes[$k]);
		}

		while($request = array_pop($this->requests)) {
			$this->config->each($this->browser);

			$rid = $this->getRequestId($request);
			$this->done[] = $rid;

			$coverage->start($rid);
			$response = $this->browser->request($request);
			$coverage->stop();

			if($response->isOk())
				$this->console->info(strtoupper($request->method()).' '.$request->url->full().' ('.$response->getCode().')');
			else
				$this->console->comment(strtoupper($request->method()).' '.$request->url->full().' ('.$response->getCode().')');

			if($this->config->ignore404 && $response->getCode() == 404)
				continue;

			$route = $this->resolver->getRoute($request);
			$this->results->add($rid, $request, $response, $route);

			if(!$response->isOk() || !$response->getContent())
				continue;

			$content = $response->getContent();
			$doc = new \H0gar\Xpath\Doc($content);

			$base = $this->getBase($request->url->full(), $doc);

			#forms
			foreach($doc->items('//form') as $f) {
				$form = new Form($f);

				$r = new \Asgard\Http\Request;

				$action = $f->attr('action');
				$action = $this->buildUrl($_root, $base, $action);
				$p = parse_url($action);
				$path = $p['path'];
				$r->url->setUrl($path);
				if(isset($p['query'])) {
					$query = $p['query'];
					parse_str($query, $get);
					if($get !== null)
						$r->get->set($get);
				}

				$r = $form->getRequest($r);

				$this->addRequests($r);
			}

			#urls
			preg_match_all('#\bhttps?://[^\s"\'()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $response->getContent(), $match);
			foreach($match[0] as $url) {
				$url = $this->buildUrl($_root, $base, $url);
				if($url === false)
					continue;

				$r = new \Asgard\Http\Request;
				$r->setMethod('GET');

				$p = parse_url($url);
				$path = $p['path'];
				$r->url->setUrl($path);
				if(isset($p['query'])) {
					$query = $p['query'];
					parse_str($query, $get);
					$r->get->set($get);
				}

				$this->addRequests($r);
			}
		}

		$report = $coverage->getReport();
		$actionsCoverage = [];

		foreach($report as $node) {
			$this->analyseNode($node);

			if($node instanceof \PHP_CodeCoverage_Report_Node_File) {
				foreach($node->getClasses() as $className=>$class) {
					foreach($class['methods'] as $methodName=>$method) {
						if(strpos($methodName, 'Action') !== false) {
							$actionsCoverage[$className.':'.$methodName] = \PHP_CodeCoverage_Util::percent(
								$method['executedLines'],
								$method['executableLines'],
								false
							);
						}
					}
				}
			}
		}

		foreach($this->coverage as $id=>$coverage)
			$this->results->addCoverage($id, $coverage);

		$done = [];
	
		$table = new \Symfony\Component\Console\Helper\Table($this->console->getOutput());
		$table->setHeaders([
			'Route',
			'Action',
			'Coverage'
		]);

		$res = [];

		$this->console->comment("\n".'Results:');
		foreach($routes as $r) {
			$fcontroller = $r->getController();
			$controller = preg_replace('/.*\\\/', '', $fcontroller);
			$action = $r->getAction();
			$method = $action.'Action';
			$fullAction = $controller.':'.$method;
			if(in_array($fullAction, $done))
				continue;
			$done[] = $fullAction;

			if(!isset($actionsCoverage[$fullAction]))
				$coverage = '0%';
			else
				$coverage = round($actionsCoverage[$fullAction]).'%';

			$res[] = [
				$r->getRoute(),
				$fcontroller.':'.$action,
				$coverage
			];
		}

		usort($res, function($a, $b) {
			return (int)$a[2] < (int)$b[2];
		});

		foreach($res as $r)
			$table->addRow($r);

		$table->render();
	}

	protected function analyseNode($node) {
		if($node instanceof \PHP_CodeCoverage_Report_Node_File) {
			$found = false;
			foreach($this->config->coverage as $c) {
				$nPath = str_replace('/', '\\', realpath($node->getPath()));
				$root = str_replace('/', '\\', realpath($c));
				if(strpos($nPath, $root) === 0) {
					$found = true;
					break;
				}
			}
			if(!$found)
				return;
			$data = $node->getCoverageData();
			foreach($data as $l=>$names) {
				if(!$names)
					continue;
				foreach($names as $name)
					$this->coverage[$name][$node->getPath()][] = $l;
			}
		}
	}

	public function addRequests($requests) {
		$host = $this->config->host;
		$root = $this->config->root;

		if(!is_array($requests))
			$requests = [$requests];
		foreach($requests as $r) {
			if(!$this->isValid($r->url->get()))
				continue;

			$r->url->setHost($host);
			$r->url->setRoot($root);


			$id = $this->getRequestId($r);
			if(!isset($this->requests[$id]) && !in_array($id, $this->done))
				$this->requests[$id] = $r;
		}

		return $this;
	}

	public function isValid($url) {
		foreach($this->config->exclusions as $e) {
			if($this->match($url, $e))
				return false;
		}
		$found = !$this->config->inclusions;
		foreach($this->config->inclusions as $e) {
			if($this->match($url, $e)) {
				$found = true;
				break;
			}
		}
		return $found;
	}

	public function match($url, $pattern) {
		$pattern = preg_quote($pattern, '/');
		$pattern = str_replace('\*', '.*', $pattern);
		return preg_match('/^'.$pattern.'$/', $url);
	}
}