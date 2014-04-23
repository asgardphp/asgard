<%
namespace <?php echo $bundle['namespace'] ?>\Controllers;

/**
@Prefix('<?php echo $entity['meta']['plural'] ?>')
*/
class <?php echo ucfirst($entity['meta']['name']) ?>Controller extends \Asgard\Core\Controller {<?php if(in_array('index', $entity['front'])): ?>
	/**
	@Route('')
	*/
	public function indexAction($request) {
		$this-><?php echo $entity['meta']['plural'] ?> = <?php echo ucfirst($entity['meta']['name']) ?>::all();
	}
	<?php endif ?><?php if(in_array('details', $entity['front'])): ?>
	/**
	@Route(':id')
	*/
	public function detailsAction($request) {
		if(!($this-><?php echo $entity['meta']['name'] ?> = <?php echo ucfirst($entity['meta']['name']) ?>::load($request['id'])))
			$this->notfound();
			
		// $this-><?php echo $entity['meta']['name'] ?>->showMetas();
		// SEO::canonical($this, $this-><?php echo $entity['meta']['name'] ?>->url());
	}<?php endif ?>
}