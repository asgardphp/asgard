<?php
namespace Asgard\Email;

class SwiftEmail implements DriverInterface {
	protected $transport;

	public function transport($transport) {
		$this->transport = $transport;
	}

	public function send($cb) {
		if(isset($this->transport['transport']) && $this->transport['transport'] == 'smtp') {
			$host = isset($this->transport['host']) ? $this->transport['host']:'localhost';
			$port = isset($this->transport['port']) ? $this->transport['port']:25;
			$security = isset($this->transport['security']) ? $this->transport['security']:null;


			$transport = \Swift_SmtpTransport::newInstance($host, $port, $security);

			if(isset($this->transport['username']))
				$transport->setUsername($this->transport['username']);
			if(isset($this->transport['password']))
				$transport->setUsername($this->transport['password']);
		}
		elseif(isset($this->transport['transport']) && $this->transport['transport'] == 'sendmail')
			$transport = \Swift_SendmailTransport::newInstance($this->transport['command']);
		else
			$transport = \Swift_MailTransport::newInstance();

		$mailer = \Swift_Mailer::newInstance($transport);

		$message = new SwiftMessage();

		$cb($message);

		return $mailer->send($message);
	}
}
