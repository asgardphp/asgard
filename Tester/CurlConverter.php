<?php
namespace Asgard\Tester;

class CurlConverter {
	protected function match($pattern, $str) {
		preg_match($pattern, $str, $this->matches);
		if(!isset($this->matches[1]))
			return;
		return $this->matches[1];
	}

	protected function matchMany($pattern, $str) {
		preg_match_all($pattern, $str, $this->matches);
		return $this->matches[1];
	}

	protected function outputPHP($v, $tabs=0, $line=false) {
		$r = '';

		if($line)
			$r .= "\n".str_repeat("\t", $tabs);

		if(is_array($v)) {
			$r .= '[';
			if($v === array_values($v)) {
				foreach($v as $_v)
					$r .= $this->outputPHP($_v, $tabs+1, true).",";
			}
			else {
				foreach($v as $_k=>$_v)
					$r .= $this->outputPHP($_k, $tabs+1, true).' => '.$this->outputPHP($_v, $tabs+1).",";
			}
			$r .= "\n".str_repeat("\t", $tabs).']';

			return $r;
		}
		elseif(is_object($v))
			return '[object] '.get_class($v);
		else
			return $r.var_export($v, true);
	}

	protected function parse_raw_http_request($input, $contentType, array &$post, array &$files) {
		// grab multipart boundary from content type header
		preg_match('/boundary=(.*)$/', $contentType, $this->matches);
		$boundary = $this->matches[1];

		// split content by boundary and get rid of last -- element
		$a_blocks = preg_split("/-+$boundary/", $input);
		array_pop($a_blocks);

		// loop data blocks
		foreach ($a_blocks as $id => $block) {
			if (empty($block))
				continue;

			preg_match('/name=\"([^\"]*)\".*[\n|\r]+([^\n\r].*)?\r$/s', $block, $this->matches);
			if(!isset($this->matches[2]))
				$this->matches[2] = null;
			if(strpos($block, '; filename=') !== false) {
				$name = $this->matches[1];
				preg_match('/filename="(.*?)"/s', $block, $this->matches);
				$filename = $this->matches[1];
				preg_match('/Content-Type: (.*?)\n/s', $block, $this->matches);
				$type = trim($this->matches[1]);
				$files[$name] = [
					'name' => $filename,
					'type' => $type,
					'tmp_name' => null,
					'error' => null,
					'size' => null,
				];
			}
			else
				$post[$this->matches[1]] = $this->matches[2];
		}
	}

	public function convert($curl) {
		$curl = str_replace('"%"', '%', $curl);
		$curl = urldecode($curl);

		$_url = $this->match('/curl "(.*?)(?<!%)"/', $curl);
		$p = parse_url($_url);
		$url = trim($p['path'].(isset($p['query']) ? '?'.$p['query']:''), '/');
		$h = $this->matchMany('/(-H ".*?" )/s', $curl);
		foreach($h as $k=>$v) {
			$v = $this->match('/-H "(.*)(?<!%)"/', $v);
			$v = trim($v);
			$v = trim($v, '"');
			$h[$k] = $v;
		}
		$headers = $cookies = [];
		foreach($h as $v) {
			$e = explode(': ', $v);
			$name = $e[0];
			$value = implode(': ', array_slice($e, 1));

			$headers[$name] = $value;
			if($name === 'Cookie') {
				$c = explode(';', $value);
				foreach($c as $cv) {
					$e = explode('=', $cv);
					$cname = trim($e[0]);
					$cvalue = trim(implode(': ', array_slice($e, 1)));
					$cvalue = trim($cvalue);
					$cvalue = trim($cvalue, '"');
					$cookies[$cname] = $cvalue;
				}
			}
		}
		$body = $this->match('/--data "(.*)"/s', $curl);
		if(!$body) {
			$body = $this->match('/--data-binary "(.*)"/s', $curl);
			$body = preg_replace("/\n\"/", '', $body);
			$body = preg_replace("/\"\^/", '', $body);
			$body = str_replace('""', '"', $body);
		}
		$post = [];
		$files = [];
		parse_str($body, $post);
		if(!$post && isset($headers['Content-Type']))
			$this->parse_raw_http_request($body, $headers['Content-Type'], $post, $files);
		if($post || $files)
			$body = '';
		if($post)
			$method = 'POST';
		else
			$method = 'GET';

		$request = "\$browser = \$this->createBrowser();";
		if($cookies)
			$request .= '
$browser->getCookies()->setAll('.$this->outputPHP($cookies).');';
		$request .= "
\$response = \$browser->req(
	'".$url."',
	'".$method."'";
		if($post || $files || $headers)
			$request .= ', '.$this->outputPHP($post, 1);
		if($files || $headers)
			$request .= ', '.$this->outputPHP($files, 1);
		if($headers)
			$request .= ', \'\', '.$this->outputPHP($headers, 1);
		$request .= ');';
		$request .= "
\$this->assertTrue(\$response->isOk());";

		return $request;
	}

}