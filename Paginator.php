<?php
namespace Coxis\Utils;

class Paginator {
	public $per_page;
	public $total;
	public $page;
	
	#todo use the same api as orm::paginate
	function __construct($per_page, $total, $page) {
		$this->per_page		= $per_page;
		$this->total		= $total;
		$this->page		= $page ? $page:1;
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
		$url = \URL::current();
		if($this->page > 1)
			echo '<a href="'.$url.'?'.http_build_query(array_merge(\GET::all(), array('page'=>$this->page-1))).'">«</a>';
		for($i=1; $i<=$this->getPages(); $i++)
			echo '<a href="'.$url.'?'.http_build_query(array_merge(\GET::all(), array('page'=>$i))).'" '.($this->page ==$i ? 'class="active"':'').'>'.$i.'</a>';
		if($this->page < $this->getPages())
			echo '<a href="'.$url.'?'.http_build_query(array_merge(\GET::all(), array('page'=>$this->page+1))).'">»</a>';
	}
	
	public function hasPrev() {
		return ($this->page > 1);
	}
	
	public function hasNext() {
		return ($this->page < $this->getPages());
	}
	
	public function getPrev() {
		$url = Router::current();
		return $url.'?'.http_build_query(array_merge(\GET::all(), array('page'=>$this->page-1)));
	}
	
	public function getNext() {
		$url = Router::current();
		return $url.'?'.http_build_query(array_merge(\GET::all(), array('page'=>$this->page+1)));
	}
	
	#todo remove (only for arpa)
	public function display($url) {
		if($this->getPages() == 1)
			return;
		$url = $url.'?page=';
		$p = $this->page;
		?>
		<ul class="paging">
			<?php if($p>1): ?>
			<li><a href="<?php echo $url.($p-1) ?>">précédent</a></li>
			<?php endif ?>
			<?php for($i=1; $i<=$this->getPages(); $i++): ?>
			<?php if($p==$i): ?>
			<li class="active"><a href="<?php echo $url.$i ?>"><?php echo (string)$i ?></a></li>
			<?php else: ?>
			<li><a href="<?php echo $url.$i ?>"><?php echo (string)$i ?></a></li>
			<?php endif ?>
			<?php endfor ?>
			<?php if($p<$this->getPages()): ?>
			<li><a href="<?php echo $url.($p+1) ?>">suivant</a></li>
			<?php endif ?>
		</ul>
		<?php
	}
}