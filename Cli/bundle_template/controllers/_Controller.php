<%
namespace <?php echo $bundle['namespace'] ?>\Controllers;

<?php if(($prefix=$controller['prefix']) !== null): ?>
/**
@Prefix('<?php echo $prefix ?>')
*/
<?php endif ?>
class <?php echo ucfirst($controller['name']) ?> extends \Asgard\Http\Controller {
<?php foreach($controller['actions'] as $name=>$action): ?>
<?php if(($route=$action['route']) !== null): ?>
	/**
	@Route('<?php echo $route ?>')
	*/
<?php endif ?>
	public function <?php echo $name ?>Action($request) {
	}

<?php endforeach ?>
}