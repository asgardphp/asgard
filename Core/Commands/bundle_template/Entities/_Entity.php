<%
namespace <?=$bundle['namespace'] ?>\Entities;

class <?=ucfirst($entity['meta']['name']) ?> extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
<?php foreach($entity['properties'] as $name=>$property): ?>
		'<?=$name ?>'	=>	[
<?php foreach($property as $k=>$v): ?>
			'<?=$k ?>'	=>	<?=$this->outputPHP($v) ?>,
<?php endforeach ?>
		],
<?php endforeach ?>
		];

		$definition->relations = [
	<?php foreach($entity['relations'] as $relationname => $relation): ?>
			'<?=$relationname ?>' => array(
				<?php foreach($relation as $k=>$v): ?>
				'<?=$k ?>'	=>	<?=$this->outputPHP($v) ?>,
				<?php endforeach ?>
			),
	<?php endforeach ?>
		];

		$definition->behaviors = [
	<?php foreach($entity['behaviors'] as $behaviorname => $behavior): ?>
			new <?=$behaviorname ?>,
	<?php endforeach ?>
		];
		
	<?php if(isset($entity['meta']['order_by'])): ?>
		$definitin->order_by = <?=$entity['meta']['order_by'] ?>;
	<?php endif ?>
	}
	
	public function __toString() {
		return (string)$this-><?=$entity['meta']['name_field'] ?>;
	}

	public function url() {
		return static::$container['resolver']->url_for(['<?=$bundle['namespace'].'\Controllers\\'.ucfirst($entity['meta']['name']).'Controller' ?>', 'show'], array('id'=>$this->id));
	}
}