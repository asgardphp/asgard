<?php
namespace Asgard\Tester;

class Coverage {
	protected $coverage;
	protected $total;

	public function __construct($coverage=[]) {
		$this->coverage = $coverage;
	}

	public function count() {
		if($this->total)
			return $this->total;

		$tot = 0;

		foreach($this->coverage as $cov) {
			$tot += count($cov);
		}

		return $this->total = $tot;
	}

	public function getCoverage() {
		return $this->coverage;
	}

	public function add($coverage) {
		$coverage = $coverage->getCoverage();

		foreach($coverage as $k=>$v) {
			if(!isset($this->coverage[$k]))
				$this->coverage[$k] = $v;
			else
				$this->coverage[$k] = array_unique(array_merge($this->coverage[$k], $v));
		}
	}

	public function hasMoreThan($coverage) {
		$c1 = $this->coverage;
		$c2 = $coverage->getCoverage();

		foreach($c1 as $k=>$v) {
			if(!isset($c2[$k]))
				return true;
			else {
				foreach($v as $j) {
					if(!in_array($j, $c2[$k]))
						return true;
				}
			}
		}

		return false;
	}
}