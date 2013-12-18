<?php
namespace Coxis\Utils;

class Paginator {
	public $per_page;
	public $total;
	public $page;
	
	function __construct($total, $page=1, $per_page=10) {
		$this->per_page	= $per_page;
		$this->total    = $total;
		$this->page     = $page ? $page:1;
	}
	
	public function getStart() {
		return ($this->page-1)*$this->per_page;
	}
	
	public function getLimit() {
		return $this->per_page;
	}
	
	public function getPages() {
		return ceil($this->total/$this->per_page);
	}
	
	public function getFirstNbr() {
		$first = $this->getStart()+1;
		if($first > $this->total)
			return $this->total;
		else
			return $first;
	}
	
	public function getLastNbr() {
		$last = $this->getStart()+$this->getLimit();
		if($last > $this->total)
			return $this->total;
		else
			return $last;
	}
	
	public function show() {
		$r = '';
		if($this->page > 1)
			$r .= '<a href="'.$this->getPrev().'">«</a>';
		for($i=1; $i<=$this->getPages(); $i++)
			$r .= '<a href="'.\URL::full(array('page'=>$i)).'"'.($this->page ==$i ? ' class="active"':'').'>'.$i.'</a>';
		if($this->page < $this->getPages())
			$r .= '<a href="'.$this->getNext().'">»</a>';
		return $r;
	}
	
	public function hasPrev() {
		return ($this->page > 1);
	}
	
	public function hasNext() {
		return ($this->page < $this->getPages());
	}
	
	public function getPrev() {
		return \URL::full(array('page'=>$this->page-1));
	}
	
	public function getNext() {
		return \URL::full(array('page'=>$this->page+1));
	}
}