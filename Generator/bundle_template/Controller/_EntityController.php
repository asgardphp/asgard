<%
namespace <?=$bundle['namespace'] ?>\Controller;

/**
 * @Prefix("<?=$entity['meta']['plural'] ?>")
 */
class <?=ucfirst($entity['meta']['name']) ?> extends \Asgard\Http\Controller {
<?php if(in_array('index', $entity['front']) || isset($entity['front']['index'])): ?>
	/**
	 * @Route("")
	 */
	public function indexAction(\Asgard\Http\Request $request) {
		$page = $request->get['page'] ? $request->get['page']:1;
		$orm = \<?=$entity['meta']['entityClass'] ?>::paginate($page, 10);
		$this-><?=$entity['meta']['plural'] ?> = $orm->get();
		$this->paginator = $orm->getPaginator();
	}
<?php endif ?><?php if(in_array('show', $entity['front']) || isset($entity['front']['show'])): ?>

	/**
	 * @Route(":id")
	 */
	public function showAction(\Asgard\Http\Request $request) {
		if(!($this-><?=$entity['meta']['name'] ?> = \<?=$entity['meta']['entityClass'] ?>::load($request['id'])))
			$this->notfound();
	}
<?php endif ?>
}