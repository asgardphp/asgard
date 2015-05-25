<?php
namespace Asgard\Email;

/**
 * Send email through Swift.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class SwiftEmail implements DriverInterface {
	/**
	 * The transport options.
	 * @var \Swift_SendmailTransport|\Swift_MailTransport|\Swift_SmtpTransport
	 */
	protected $transport;

	/**
	 * {@inheritDoc}
	 */
	public function transport($transport) {
		if(isset($transport['transport']) && $transport['transport'] === 'smtp') {
			$host = isset($transport['host']) ? $transport['host']:'localhost';
			$port = isset($transport['port']) ? $transport['port']:25;
			$security = isset($transport['security']) ? $transport['security']:null;

			$swifttransport = \Swift_SmtpTransport::newInstance($host, $port, $security);

			if(isset($transport['username']))
				$swifttransport->setUsername($transport['username']);
			if(isset($transport['password']))
				$swifttransport->setPassword($transport['password']);
		}
		elseif(isset($transport['transport']) && $transport['transport'] === 'sendmail')
			$swifttransport = \Swift_SendmailTransport::newInstance($transport['command']);
		else
			$swifttransport = \Swift_MailTransport::newInstance();

		$this->transport = $swifttransport;
	}

	/**
	 * {@inheritDoc}
	 */
	public function send($cb) {
		$mailer = \Swift_Mailer::newInstance($this->transport);

		$message = new SwiftMessage();

		$cb($message);

		return $mailer->send($message);
	}
}
