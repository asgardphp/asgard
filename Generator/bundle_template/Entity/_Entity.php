<%
namespace <?=$bundle['namespace'] ?>\Entity;

class <?=ucfirst($entity['meta']['name'])?> extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = <?=$this->outputPHP($entity['properties'], 2)?>;

<?php if($entity['behaviors']): ?>

		$definition->behaviors = [
	<?php foreach($entity['behaviors'] as $behaviorname => $behavior): ?>
			new <?=$behaviorname ?>,
	<?php endforeach ?>
		];<?php endif ?><?php foreach($entity['metas'] as $metaname => $meta): ?>
		$definition-><?=$metaname?> = <?=$this->outputPHP($meta, 2)?>;
<?php endforeach ?>
	}

	public function __toString() {
		return (string)$this-><?=$entity['meta']['name_field'] ?>;
	}
<?php if($entity['front'] && in_array('show', $entity['front'])): ?>
	public function url() {
		return $this->getDefinition()->getContainer()['resolver']->url(['<?=$bundle['namespace'].'\Controller\\'.ucfirst($entity['meta']['name']) ?>', 'show'], array('id'=>$this->id));
	}
	<?php endif ?>}