<?php
namespace Asgard\Common;

/**
 * Paginator.
 */
class Paginator {
	/**
	 * Number of elements per page.
	 * @var integer
	 */
	public $per_page;
	/**
	 * Total number of elements.
	 * @var integer
	 */
	public $total;
	/**
	 * Page number.
	 * @var integer
	 */
	public $page;
	/**
	 * HTTP request.
	 * @var \Asgard\Http\Request
	 */
	protected $request;
	
	/**
	 * Constructor.
	 * @param integer                    $total
	 * @param integer                    $page
	 * @param integer                    $per_page
	 * @param null|\Asgard\Http\Request  $request
	 */
	public function __construct($total, $page=1, $per_page=10, $request=null) {
		$this->per_page	= $per_page;
		$this->total    = $total;
		$this->page     = $page ? $page:1;
		$this->request  = $request;
	}
	
	/**
	 * Get start position.
	 * @return integer
	 */
	public function getStart() {
		return ($this->page-1)*$this->per_page;
	}
	
	/**
	 * Get limit.
	 * @return integer
	 */
	public function getLimit() {
		return $this->per_page;
	}
	
	/**
	 * Get number of pages.
	 * @return integer
	 */
	public function getPages() {
		return ceil($this->total/$this->per_page);
	}
	
	/**
	 * Get first element position.
	 * @return integer
	 */
	public function getFirstNbr() {
		$first = $this->getStart()+1;
		if($first > $this->total)
			return $this->total;
		else
			return $first;
	}
	
	/**
	 * Get last element position.
	 * @return integer
	 */
	public function getLastNbr() {
		$last = $this->getStart()+$this->getLimit();
		if($last > $this->total)
			return $this->total;
		else
			return $last;
	}
	
	/**
	 * Render the pagination.
	 * @return string
	 */
	public function render() {
		$r = '';
		if($this->page > 1)
			$r .= '<a href="'.$this->getPrev().'">«</a>';
		for($i=1; $i<=$this->getPages(); $i++)
			$r .= '<a href="'.$this->request->url->full(['page'=>$i]).'"'.($this->page ==$i ? ' class="active"':'').'>'.$i.'</a>';
		if($this->page < $this->getPages())
			$r .= '<a href="'.$this->getNext().'">»</a>';
		return $r;
	}
	
	/**
	 * Check if has previous page.
	 * @return boolean
	 */
	public function hasPrev() {
		return ($this->page > 1);
	}
	
	/**
	 * Check if has next page.
	 * @return boolean
	 */
	public function hasNext() {
		return ($this->page < $this->getPages());
	}
	
	/**
	 * Get the previous page url.
	 * @return string
	 */
	public function getPrev() {
		return $this->request->url->full(['page'=>$this->page-1]);
	}
	
	/**
	 * Get the next page url.
	 * @return string
	 */
	public function getNext() {
		return $this->request->url->full(['page'=>$this->page+1]);
	}
}