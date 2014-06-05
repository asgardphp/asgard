<%
namespace <?php echo $bundle['namespace'] ?>\Controllers;

/**
@Prefix('<?php echo $entity['meta']['plural'] ?>')
*/
class <?php echo ucfirst($entity['meta']['name']) ?>Controller extends \Asgard\Http\Controller {<?php if(in_array('index', $entity['front']) || isset($entity['front']['index'])): ?>
	/**
	@Route('')
	*/
	public function indexAction(\Asgard\Http\Request $request) {
		$page = $request->get['page'] ? $request->get['page']:1;
		$orm = \<?php echo $entity['meta']['entityClass'] ?>::paginate($page, 10);
		$this-><?php echo $entity['meta']['plural'] ?> = \<?php echo $entity['meta']['entityClass'] ?>::get();
		$this->paginator = $orm->getPaginator();
	}
	<?php endif ?><?php if(in_array('show', $entity['front']) || isset($entity['front']['show'])): ?>
	/**
	@Route(':id')
	*/
	public function showAction(\Asgard\Http\Request $request) {
		if(!($this-><?php echo $entity['meta']['name'] ?> = \<?php echo $entity['meta']['entityClass'] ?>::load($request['id'])))
			$this->notfound();
			
		// $this-><?php echo $entity['meta']['name'] ?>->showMetas();
		// SEO::canonical($this, $this-><?php echo $entity['meta']['name'] ?>->url());
	}<?php endif ?>
}