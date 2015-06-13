#Email

[![Build Status](https://travis-ci.org/asgardphp/email.svg?branch=master)](https://travis-ci.org/asgardphp/email)

- [Installation](#installation)
- [Usage in the Asgard Framework](#usage-asgard)
- [Usage outside the Asgard Framework](#usage-outside)
- [Send an email](#send)
- [Attach files](#files)
- [Attach images](#images)
- [Fake mail](#fake)

<a name="installation"></a>
##Installation
**If you are working on an Asgard project you don't need to install this library as it is already part of the standard libraries.**

	composer require asgard/email 0.*

<a name="usage-asgard"></a>
##Usage in the Asgard Framework

###Configuration

In a configuration file, in config/, add:

	#smtp
	email:
		driver: Asgard\Email\SwiftEmail
		transport => smtp
		username  => ...
		password  => ...
		security  => ssl #or null
		host      => ...
		port      => ...
	#sendmail
	email:
		driver: Asgard\Email\SwiftEmail
		transport => sendmail
		command  => ...
	#mail()
	email:
		driver: Asgard\Email\SwiftEmail

###Service

	$email = $container['email'];
	
The [container](docs/container) is often accessible as a method parameter or through a [ContainerAware](docs/container#containeraware) object. You can also use the [singleton](docs/container#usage-outside) but it is not recommended.

<a name="usage-outside"></a>
##Usage outside the Asgard Framework

###Configuration

	#smtp
	$config = [
		'transport' => 'smtp',
		'username'  => '...',
		'password'  => '...',
		'security'  => 'ssl', #or null
		'host'      => '...',
		'port'      => '...',
	];
	#sendmail
	$config = [
		'transport' => 'sendmail',
		'command'  => '...',
	];
	#mail()
	$config = [];

###Instance

	$email = new \Asgard\Email\SwiftEmail;
	$email->transport($config);

<a name="send"></a>
##Sending an email

	$email->send(function($message) {
		$message->to('bob@example.com');
		$message->from('joe@example.com');
		$message->cc('joe@example.com');
		$message->bcc('joe@example.com');
		$message->text('hello!');
		$message->html('<h1>hello!</h1>');
	});

$message inherits \Swift_Message so you can use any of its methods as well. [See its documentation](http://swiftmailer.org/docs/messages.html).

<a name="files"></a>
##Attaching files

Directly attach a file:

	$email->send(function($message) {
		//...
		$message->attachFile('/path/to/file.jpg', 'myhouse.jpg', 'image/jpeg');
	});

Or data:

	$email->send(function($message) {
		//...
		$message->attachData($data, 'myhouse.jpg', 'image/jpeg');
	});

<a name="images"></a>
##Embedding images

Directly embed an image:

	$email->send(function($message) {
		//...
		$message->html('<h1>Hello!</h2> See my house '.$message->embedFile('/path/to/file.jpg', 'myhouse.jpg', 'image/jpeg'));
	});

Or data:

	$email->send(function($message) {
		//...
		$message->html('<h1>Hello!</h2> See my house '.$message->embedData($data, 'myhouse.jpg', 'image/jpeg'));
	});

<a name="fake"></a>
##Fake mail

For tests and development, you might want to use fake mails, not to send real emails. Fake mails are simply written on a local.

**Configuration**

	email:
		driver: Asgard\Email\FakeEmail
		file: storage/email.txt

Besides this, the usage is the same as for other emails.

###Contributing

Please submit all issues and pull requests to the [asgardphp/asgard](http://github.com/asgardphp/asgard) repository.

### License

The Asgard framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)