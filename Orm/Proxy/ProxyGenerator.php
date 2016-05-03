<?php
namespace Asgard\Orm\Proxy;

class ProxyGenerator {
	protected $proxyTemplate = '
namespace <namespace>;

class <shortName> extends <class> implements \Asgard\Orm\Proxy\ProxyInterface {
	public $__initialized__ = false;
	public $__initializer__;
	public $__dataMapper__;
	public $__class__;

	public function __initialize__() {
		$this->__dataMapper__->initializeEntityProxy($this);
		$this->__initialized__ = true;
	}

	public function getClass() {
		return $this->__class__;
	}

	public function get($name, $locale=null, $hook=true) {
		if($name !== \'id\' && !$this->__initialized__)
			$this->__initialize__($this);

		return parent::get($name, $locale, $hook);
	}
}
';

	protected $initializedClasses = [];

	public function createProxy($dataMapper, $class, $id) {
		$class = trim($class, '\\');

		if(!in_array($class, $this->initializedClasses)) {
			$reflection = new \ReflectionClass($class);

			$template = $this->proxyTemplate;
			$namespace = 'Asgard\Orm\_Proxy\\'.$reflection->getNamespaceName();
			$shortName = $reflection->getShortName();
			$proxyClass = $namespace.'\\'.$shortName;

			if(!class_exists($proxyClass, false)) {
				$template = str_replace('<namespace>', $namespace, $template);
				$template = str_replace('<shortName>', $shortName, $template);
				$template = str_replace('<class>', '\\'.$class, $template);

				eval($template);
			}

			$this->initializedClasses[] = $class;
		}

		$entityProxy = new $proxyClass(['id' => $id]);
		$entityProxy->__class__ = $class;
		$entityProxy->__dataMapper__ = $dataMapper;

		return $entityProxy;
	}
}