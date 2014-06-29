<?php
namespace Asgard\Email;

class SwiftMessage extends \Swift_Message {
	public function subject($subject) {
		return parent::setSubject($subject);
	}

	public function to($to) {
		return parent::setTo($to);
	}

	public function from($from) {
		return parent::setFrom($from);
	}

	public function cc($cc) {
		return parent::setCc($cc);
	}

	public function bcc($bcc) {
		return parent::setBcc($bcc);
	}

	public function html($html) {
		return parent::addPart($html, 'text/html');
	}

	public function text($text) {
		return parent::addPart($text, 'text/plain');
	}

	public function htmlTemplate($template, $data=[]) {
		$data['message'] = $this;
		$res = $this->buildTemplate($template, $data);
		return $this->html($res);
	}

	public function textTemplate($template, $data=[]) {
		$data['message'] = $this;
		$res = $this->buildTemplate($template, $data);
		return $this->text($res);
	}

	protected function buildTemplate($file, $data) {
		extract($data);
		ob_start();
		include $file;
		return ob_get_clean();
	}

	public function attachFile($file, $options=[]) {
		$attachment = \Swift_Attachment::fromPath($file);
		if(isset($options['filename']))
			$attachment->setFilename($options['filename']);
		if(isset($options['mime']))
			$attachment->setContentType($options['mime']);
		return parent::attach($attachment);
	}

	public function attachData($data, $options=[]) {
		$attachment = \Swift_Attachment::newInstance($data);
		if(isset($options['filename']))
			$attachment->setFilename($options['filename']);
		if(isset($options['mime']))
			$attachment->setContentType($options['mime']);
		return parent::attach($attachment);
	}

	public function embedFile($file, $options=[]) {
		$image = \Swift_Image::fromPath($file);
		if(isset($options['filename']))
			$image->setFilename($options['filename']);
		if(isset($options['mime']))
			$image->setContentType($options['mime']);
		return parent::embed($image);
	}

	public function embedData($data, $options=[]) {
		$image = \Swift_Image::newInstance($data);
		if(isset($options['filename']))
			$image->setFilename($options['filename']);
		if(isset($options['mime']))
			$image->setContentType($options['mime']);
		return parent::embed($image);
	}
}