<?php
namespace Asgard\Email;

class Email {
	protected $to;
	protected $from;
	protected $subject;
	protected $text = '';
	protected $html = '';
	protected $files = array();
 
	public function __construct($to, $from, $subject, $text='', $html='') {
 		$this->to = $to;
 		$this->from = $from;
		$this->subject = $subject;
 		$this->text = $text;
 		$this->html = $html;
 	}
 
	public static function create($to, $from, $subject, $text='', $html='') {
		$mail = new static($to, $from, $subject, $text, $html);
 		return $mail;
 	}

	public function text($text) {
		$this->text = $text;
		return $this;
 	}
 
	public function html($html) {
		$this->html = $html;
		return $this;
 	}

	public function addFile($file, $filename=null) {
		if($filename)
			$this->files[$filename] = $file;
		else
			$this->files[] = $file;
		return $this;
	}
	
	public function send() {
		$boundary = md5(uniqid(microtime(), TRUE));

		// Headers
		$headers = 'From: '.$this->from."\r\n";
		$headers .= 'Mime-Version: 1.0'."\r\n";
		$headers .= 'Content-Type: multipart/alternative;boundary='.$boundary."\r\n";
		$headers .= "This is a MIME encoded message.\r\n\r\n"; 

		#text
		if($this->text) {
			$headers .= '--'.$boundary."\r\n";
			$headers .= 'Content-Type: text/plain; charset=utf-8'."\r\n\r\n";
			$headers .= $this->text."\r\n\r\n";
		}

		#html
		if($this->html) {
			$headers .= '--'.$boundary."\r\n";
			$headers .= 'Content-Type: text/html; charset=utf-8'."\r\n\r\n";
			$headers .= $this->html."\r\n\r\n";
		}

		#files
		if($this->files) {
			foreach($this->files as $filename=>$path) {
				if(is_int($filename))
					$filename = basename($path);

				$headers .= '--'.$boundary."\r\n";
				$fp = fopen($path,"rb");
				$data = fread($fp,filesize($path));
				fclose($fp);
				$data = chunk_split(base64_encode($data));
				$headers .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n" . 
				"Content-Description: ".$filename."\r\n" .
				"Content-Disposition: attachment;\n" . " filename=\"".$filename."\"; size=".filesize($path).";\r\n" . 
				"Content-Transfer-Encoding: base64\r\n\r\n" . $data . "\r\n\r\n";
			}
		}
		
		$headers .= '--'.$boundary."--";

		return mail($this->to, '=?utf-8?B?'.base64_encode($this->subject).'?=', '', $headers);
	}
}
