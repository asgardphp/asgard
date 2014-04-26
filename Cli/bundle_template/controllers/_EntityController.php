<%
namespace <?php echo $bundle['namespace'] ?>\Controllers;

/**
@Prefix('<?php echo $entity['meta']['plural'] ?>')
*/
class <?php echo ucfirst($entity['meta']['name']) ?>Controller extends \Asgard\Core\Controller {<?php if(in_array('index', $entity['front']) || isset($entity['front']['index'])): ?>
	/**
	@Route('')
	*/
	public function indexAction($request) {
		$this-><?php echo $entity['meta']['plural'] ?> = \<?php echo $entity['meta']['entityClass'] ?>::all();
	}
	<?php endif ?><?php if(in_array('show', $entity['front']) || isset($entity['front']['show'])): ?>
	/**
	@Route(':id')
	*/
	public function showAction($request) {
		if(!($this-><?php echo $entity['meta']['name'] ?> = \<?php echo $entity['meta']['entityClass'] ?>::load($request['id'])))
			$this->notfound();
			
		// $this-><?php echo $entity['meta']['name'] ?>->showMetas();
		// SEO::canonical($this, $this-><?php echo $entity['meta']['name'] ?>->url());
	}<?php endif ?>
}