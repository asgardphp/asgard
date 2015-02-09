<?php
namespace Asgard\Form;

interface WidgetFactoryInterface {
	public function create($name, $value, $options, $form);
}