<?php
namespace Asgard\Http\Utils;

/**
 * Store messages in the container and display them on the next page.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Flash {
	/**
	 * Messages.
	 * @var array
	 */
	protected $messages = [];
	/**
	 * container dependency.
	 * @var \Asgard\Container\ContainerInterface
	 */
	protected $container;
	/**
	 * Display callback.
	 * @var callable
	 */
	protected $cb;
	/**
	 * Global display callback.
	 * @var callable
	 */
	protected $globalCb;
	/**
	 * Fetched flag.
	 * @var boolean
	 */
	protected $fetched;

	/**
	 * Constructor.
	 * @param \Asgard\Container\ContainerInterface $container
	 */
	public function __construct(\Asgard\Container\ContainerInterface $container) {
		$this->container = $container;
	}

	/**
	 * Fetch messages.
	 * @return Flash $this
	 */
	protected function fetch() {
		if($this->fetched)
			return;
		if($this->container['session']->has('messages'))
			$this->messages = $this->container['session']['messages'];
		$this->fetched = true;
		return $this;
	}

	/**
	 * Persist messages into the container.
	 * @return string
	 */
	protected function persist() {
		$this->container['session']['messages'] = $this->messages;
	}

	/**
	 * Add a success message.
	 * @param string $message
	 */
	public function addSuccess($message) {
		return $this->add('success', $message);
	}

	/**
	 * Add an error message.
	 * @param string $message
	 */
	public function addError($message) {
		return $this->add('error', $message);
	}

	/**
	 * Add an info message.
	 * @param string $message
	 */
	public function addInfo($message) {
		return $this->add('info', $message);
	}

	/**
	 * Add a warning message.
	 * @param string $message
	 */
	public function addWarning($message) {
		return $this->add('warning', $message);
	}

	/**
	 * Add a custom type message.
	 * @param string $type
	 * @param string $message
	 */
	public function add($type, $message) {
		$this->fetch();

		if(isset($message[$type]) && is_array($message[$type]))
			$this->messages[$type] = array_merge($this->messages[$type], $message);
		else
			$this->messages[$type][] = $message;

		$this->persist();
		return true;
	}

	/**
	 * Show all messages.
	 * @param string  $cat
	 * @param boolean $cb  Use the global callback.
	 */
	public function showAll($cat=null, $cb=true) {
		$this->fetch();
		if($cb && $this->globalCb) {
			$globalCb = $this->globalCb;
			$globalCb($this, $cat);
		}
		else {
			foreach($this->messages as $type=>$messages)
				$this->show($type, $cat);
		}
	}

	/**
	 * Show a custom type messages.
	 * @param  string $type
	 * @param  string $cat
	 * @param  callable $cb
	 */
	public function show($type, $cat=null, $cb=null) {
		$this->fetch();
		
		if(!$cb)
			$cb = $this->cb;
		if($cat)
			$messages = isset($this->messages[$type][$cat]) ? \Asgard\Common\ArrayUtils::flatten($this->messages[$type][$cat]):[];
		else
			$messages = isset($this->messages[$type]) ? \Asgard\Common\ArrayUtils::flatten($this->messages[$type]):[];
		foreach($messages as $msg) {
			if($cb)
				echo $cb($msg, $type);
			else
				echo '<div class="flash '.$this->getClass($type).'" role="alert">'.$msg.'</div>'."\n";
		}
		if($cat)
			unset($this->messages[$type][$cat]);
		else
			$this->messages[$type] = [];
		$this->persist();
	}

	/**
	 * Return the CSS class.
	 * @param  string $type
	 * @return string
	 */
	protected function getClass($type) {
		$types = [
			'error' => 'error',
			'success' => 'success',
			'info' => 'info',
			'warning' => 'warning',
		];
		if(!isset($types[$type]))
			return '';
		return $types[$type];
	}

	/**
	 * Set the display callback.
	 * @param  callable $cb
	 * @return Flash $this
	 */
	public function setCallback(callable $cb) {
		$this->cb = $cb;
		return $this;
	}

	public function has($type=null) {
		if($type === null)
			return count($this->messages) > 0;
		else
			return isset($this->messages['type']) && count($this->messages['type']) > 0;
	}
	public function setGlobalCallback(callable $globalCb) {
		$this->globalCb = $globalCb;
		return $this;
	}
}