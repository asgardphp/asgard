<%
namespace <?php echo $bundle['namespace'] ?>\Entities;

class <?php echo ucfirst($entity['meta']['name']) ?> extends \Asgard\Entity\Entity {
	public static function definition($definition) {
		$definition->properties = array(
<?php foreach($entity['properties'] as $name=>$property): ?>
		'<?php echo $name ?>'	=>	array(
<?php foreach($property as $k=>$v): ?>
			'<?php echo $k ?>'	=>	<?php echo BuildTools::outputPHP($v) ?>,
<?php endforeach ?>
		),
<?php endforeach ?>
		);

		$definition->relations = array(	
	<?php foreach($entity['relations'] as $relationname => $relation): ?>
			'<?php echo $relationname ?>' => array(
				<?php foreach($relation as $k=>$v): ?>
				'<?php echo $k ?>'	=>	<?php echo BuildTools::outputPHP($v) ?>,
				<?php endforeach ?>
			),
	<?php endforeach ?>
		);

		$definition->behaviors = array(	
	<?php foreach($entity['behaviors'] as $behaviorname => $behavior): ?>
			new \<?php echo $behaviorname ?>,
	<?php endforeach ?>
		);
		
	<?php if(isset($entity['meta']['order_by'])): ?>
		$definitin->order_by = <?php echo $entity['meta']['order_by'] ?>;
	<?php endif ?>
	}
	
	public function __toString() {
		return (string)$this-><?php echo $entity['meta']['name_field'] ?>;
	}

	public function url() {
		return static::$app['resolver']->url_for(array('<?php echo ucfirst($entity['meta']['name']) ?>', 'show'), array('id'=>$this->id));
	}
}