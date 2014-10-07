<?php
namespace Asgard\Http\Utils;

/**
 * Store messages in the session and display them on the next page.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Flash {
	/**
	 * Messages.
	 * @var array
	 */
	protected $messages = [];
	/**
	 * Session dependency.
	 * @var \Asgard\Common\BagInterface
	 */
	protected $session;
	/**
	 * Display callback.
	 * @var callable
	 */
	protected $cb;

	/**
	 * Constructor.
	 * @param \Asgard\Http\SessionManager $session
	 */
	public function __construct(\Asgard\Common\BagInterface $session) {
		$this->session = $session;
		if($session->has('messages'))
			$this->messages = $session['messages'];
	}

	/**
	 * Persist messages into the session.
	 * @return string
	 */
	protected function persist() {
		$this->session['messages'] = $this->messages;
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
		if(isset($message[$type]) && is_array($message[$type]))
			$this->messages[$type] = array_merge($this->messages[$type], $message);
		else
			$this->messages[$type][] = $message;

		$this->persist();
		return true;
	}

	/**
	 * Show all messages.
	 * @param  string $cat
	 */
	public function showAll($cat=null) {
		foreach($this->messages as $type=>$messages)
			$this->show($type, $cat);
	}

	/**
	 * Show a custom type messages.
	 * @param  string $type
	 * @param  string $cat
	 * @param  callable $cb
	 */
	public function show($type, $cat=null, $cb=null) {
		if($cat)
			$messages = isset($this->messages[$type][$cat]) ? \Asgard\Common\ArrayUtils::flatten($this->messages[$type][$cat]):[];
		else
			$messages = isset($this->messages[$type]) ? \Asgard\Common\ArrayUtils::flatten($this->messages[$type]):[];
		foreach($messages as $msg) {
			if($cb)
				echo $cb($msg, $type);
			else
				echo '<div class="flash '.$type.'">'.$msg.'</div>'."\n";
		}
		if($cat)
			unset($this->messages[$type][$cat]);
		else
			$this->messages[$type] = [];
		$this->persist();
	}
}