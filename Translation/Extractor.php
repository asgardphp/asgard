<?php
namespace Asgard\Translation;

class Extractor {
	public $strings = [];

	public function getStrings() {
		$this->strings = array_unique($this->strings);
		return $this->strings;
	}

	protected function ext($stmt) {
		if($stmt instanceof \PhpParser\Node\Expr\FuncCall) {
			if(isset($stmt->name->parts[0]) && in_array($stmt->name->parts[0], ['__', 'trans'])) {
				if(!isset($stmt->args[0]->value->value)) {
					$prettyPrinter = new \PhpParser\PrettyPrinter\Standard;
					echo 'Warning - This is not a string: '.$prettyPrinter->prettyPrint([$stmt->args[0]->value])."\n";
				}
				else
					$this->strings[] = $stmt->args[0]->value->value;
				$args = array_slice($stmt->args, 1);
			}
			else
				$args = $stmt->args;

			foreach($args as $a)
				$this->ext($a);
		}
		elseif(is_array($stmt)) {
			foreach($stmt as $v)
				$this->ext($v);
		}
		elseif(!is_object($stmt))
			return;
		else {
			foreach($stmt->getSubNodeNames() as $name)
				$this->ext($stmt->{$name});
		}
	}

	public function parse($file) {
		try {
			$code = file_get_contents($file);
			$parser = new \PhpParser\Parser(new \PhpParser\Lexer);
			
			$stmts = $parser->parse($code);
			if($stmts === null)
				return;
			foreach($stmts as $stmt)
				$this->ext($stmt);
		} catch(\PhpParser\Error $e) {
			echo 'Warning - Following file is invalid: '.$file."\n";
		}
	}

	public function parseDirectory($dir) {
		foreach(glob($dir.'/*') as $f) {
			if(is_dir($f))
				$this->parseDirectory($f);
			elseif(preg_match('/\.php$/', $f))
				$this->parse($f);
		}
	}

	public function addStrings(array $strings) {
		$this->strings = array_merge($this->strings, $strings);
	}

	public function getListWithTranslation($translator, $srcLocale, $dstLocale) {
		$res = [];
		foreach($this->getStrings() as $s) {
			if($translator->trans($s, [], 'messages', $dstLocale) === $s) {
				$res[] = [
					$s,
					$translator->trans($s, [], 'messages', $srcLocale),
					''
				];
			}
		}

		return $res;
	}

	public function getList($translator, $dstLocale) {
		$res = [];
		foreach($this->getStrings() as $s) {
			if($translator->trans($s, [], 'messages', $dstLocale) === $s)
				$res[$s] = '';
		}

		return $res;
	}
}