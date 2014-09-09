<?php
namespace Asgard\Email;

/**
 * Send email through Swift.
 */
class SwiftEmail implements DriverInterface {
	/**
	 * The transport options.
	 * @var array
	 */
	protected $transport;

	/**
     * {@inheritdoc}
	 */
	public function transport($transport) {
		if(isset($transport['transport']) && $transport['transport'] == 'smtp') {
			$host = isset($transport['host']) ? $transport['host']:'localhost';
			$port = isset($transport['port']) ? $transport['port']:25;
			$security = isset($transport['security']) ? $transport['security']:null;

			$transport = \Swift_SmtpTransport::newInstance($host, $port, $security);

			if(isset($transport['username']))
				$transport->setUsername($this->transport['username']);
			if(isset($transport['password']))
				$transport->setPassword($this->transport['password']);
		}
		elseif(isset($transport['transport']) && $transport['transport'] == 'sendmail')
			$transport = \Swift_SendmailTransport::newInstance($transport['command']);
		else
			$transport = \Swift_MailTransport::newInstance();

		$this->transport = $transport;
	}

	/**
     * {@inheritdoc}
	 */
	public function send($cb) {
		$mailer = \Swift_Mailer::newInstance($this->transport);

		$message = new SwiftMessage();

		$cb($message);

		return $mailer->send($message);
	}
}
