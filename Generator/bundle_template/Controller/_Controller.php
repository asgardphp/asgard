<%
namespace <?=$bundle['namespace'] ?>\Controller;

<?php if(($prefix=$controller['prefix']) !== null): ?>
/**
 * @Prefix("<?=$prefix ?>")
 */
<?php endif ?>
class <?=ucfirst($controller['name']) ?> extends \Asgard\Http\Controller {
<?php foreach($controller['actions'] as $name=>$action): ?>
<?php if(($route=$action['route']) !== null): ?>
	/**
	 * @Route("<?=$route ?>")
	*/
<?php endif ?>
	public function <?=$name ?>Action($request) {
<?php if($action['template']): ?>
		$this->view = '<?=$action['template']?>';
		<?php endif ?>
	}

<?php endforeach ?>
}