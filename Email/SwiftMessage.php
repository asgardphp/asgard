<?php
namespace Asgard\Email;

/**
 * Extended SwiftMessage class.
 */
class SwiftMessage extends \Swift_Message {
	/**
	 * Set the subject.
	 * @param  string $subject
	 * @return SwiftMessage $this
	 */
	public function subject($subject) {
		return parent::setSubject($subject);
	}

	/**
	 * Set the destinator.
	 * @param  array|string $to
	 * @return SwiftMessage $this
	 */
	public function to($to) {
		return parent::setTo($to);
	}

	/**
	 * Set the sender.
	 * @param  string $from
	 * @return SwiftMessage $this
	 */
	public function from($from) {
		return parent::setFrom($from);
	}

	/**
	 * Set the cc.
	 * @param  array|string $cc
	 * @return SwiftMessage $this
	 */
	public function cc($cc) {
		return parent::setCc($cc);
	}

	/**
	 * Set the bcc.
	 * @param  array|string $bcc
	 * @return SwiftMessage $this
	 */
	public function bcc($bcc) {
		return parent::setBcc($bcc);
	}

	/**
	 * Set the hml content.
	 * @param  string $html 
	 * @return SwiftMessage $this
	 */
	public function html($html) {
		return parent::addPart($html, 'text/html');
	}

	/**
	 * Set the text content.
	 * @param  string $text 
	 * @return SwiftMessage $this
	 */
	public function text($text) {
		return parent::addPart($text, 'text/plain');
	}

	/**
	 * Use a template for the html content.
	 * @param  string $template
	 * @param  array  $data
	 * @return string
	 */
	public function htmlTemplate($template, $data=[]) {
		$data['message'] = $this;
		$res = $this->buildTemplate($template, $data);
		return $this->html($res);
	}

	/**
	 * Use a template for the html content.
	 * @param  string $template
	 * @param  array  $data
	 * @return string
	 */
	public function textTemplate($template, $data=[]) {
		$data['message'] = $this;
		$res = $this->buildTemplate($template, $data);
		return $this->text($res);
	}

	/**
	 * Process a template.
	 * @param  string $file
	 * @param  array $data
	 * @return string
	 */
	protected function buildTemplate($file, $data) {
		extract($data);
		ob_start();
		include $file;
		return ob_get_clean();
	}

	/**
	 * Attach a file to the email.
	 * @param  string $file
	 * @param  array  $data
	 * @return SwiftMessage $this
	 */
	public function attachFile($file, $options=[]) {
		$attachment = \Swift_Attachment::fromPath($file);
		if(isset($options['filename']))
			$attachment->setFilename($options['filename']);
		if(isset($options['mime']))
			$attachment->setContentType($options['mime']);
		return parent::attach($attachment);
	}

	/**
	 * Attach data as a file to the email.
	 * @param  string $data
	 * @param  array  $options
	 * @return SwiftMessage $this
	 */
	public function attachData($data, $options=[]) {
		$attachment = \Swift_Attachment::newInstance($data);
		if(isset($options['filename']))
			$attachment->setFilename($options['filename']);
		if(isset($options['mime']))
			$attachment->setContentType($options['mime']);
		return parent::attach($attachment);
	}

	/**
	 * Embed a file in the email.
	 * @param  string $file
	 * @param  array  $options
	 * @return SwiftMessage $this
	 */
	public function embedFile($file, $options=[]) {
		$image = \Swift_Image::fromPath($file);
		if(isset($options['filename']))
			$image->setFilename($options['filename']);
		if(isset($options['mime']))
			$image->setContentType($options['mime']);
		return parent::embed($image);
	}

	/**
	 * Embed data as a file in the email.
	 * @param  string $data
	 * @param  array  $options
	 * @return SwiftMessage $this
	 */
	public function embedData($data, $options=[]) {
		$image = \Swift_Image::newInstance($data);
		if(isset($options['filename']))
			$image->setFilename($options['filename']);
		if(isset($options['mime']))
			$image->setContentType($options['mime']);
		return parent::embed($image);
	}
}