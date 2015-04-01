<?php
namespace Asgard\Tester;

class Form {
	protected $form;
	protected $action;

	public function __construct($form) {
		$this->form = $form;
	}

	public function setAction($action) {
		$this->action = $action;
	}

	public function getPath($name) {
		$path = [];
		$matches = null;
		preg_match('/^([^\[]+)/', $name, $matches);
		$path[] = $matches[0];
		preg_match_all('/\[([^\]]*)\]/', $name, $matches);
		$path = array_merge($path, $matches[1]);

		return $path;
	}

	public function getRequest($request) {
		$f = $this->form;
		$parser = new \Asgard\Http\Browser\FormParser($f->getNode());

		$vars = [];
		$file = [];

		$submitFound = false;

		foreach($parser->getFields() as $name=>$fo) {
			if($fo->getType() === 'submit' || $fo->getType() === 'image') {
				if($submitFound)
					continue;
				$submitFound = true;
			}

			if(!($value = $fo->getValue())) {
				if($choices = $fo->getChoices()) {
					$value = $choices[count($choices)-1];
				}
				else {
					switch($fo->getType()) {
						case 'file':
							$value = [
								'name' => 'file.jpg',
								'type' => 'image/jpg',
								'tmp_name' => __DIR__.'/file.jpg',
								'error' => '0',
								'size' => '10',
							];

							$path = $this->getPath($name);

							$arr =& $file;
							$key = array_pop($path);

							foreach($path as $parent)
								$arr =& $arr[$parent];
							if(!$key)
								$arr[] = $value;
							else
								$arr[$key] = $value;

							continue 2;
						case 'color':
							$value = '#ff0000';
							break;
						case 'date':
							$value = '2000-01-02';
							break;
						case 'datetime-local':
							$value = '2010-02-02T13:01';
							break;
						case 'email':
							$value = 'bob@bob.com';
							break;
						case 'month':
							$value = '2012-09';
							break;
						case 'number':
							$value = '10';
							break;
						case 'range':
							if($fo->node->getAttribute('min') !== '')
								$value = $fo->node->getAttribute('min');
							elseif($fo->node->getAttribute('max') !== '')
								$value = $fo->node->getAttribute('max');
							else
								$value = '10';
							break;
						case 'tel':
							$value = '0909090909';
							break;
						case 'time':
							$value = '02:01';
							break;
						case 'url':
							$value = 'http://www.google.com';
							break;
						case 'week':
							$value = '2010-W45';
							break;
						case 'search':
						default:
							$value = 'aaaaaa';
							break;
					}
				}
			}

			$path = $this->getPath($name);

			$arr =& $vars;
			$key = array_pop($path);

			foreach($path as $parent)
				$arr =& $arr[$parent];
			if(!$key)
				$arr[] = $value;
			else
				$arr[$key] = $value;
		}

		$method = strtoupper($f->attr('method'));
		if(!in_array($method, ['GET', 'POST', 'PUT', 'DELETE']))
			$method = 'GET';

		$get = $post = [];
		if($method === 'GET')
			$get = $vars;
		else
			$post = $vars;

		$request->setMethod($method);
		$request->post->set($post);
		$request->get->set($get);
		$request->file->set($file);
		
		return $request;
	}
}