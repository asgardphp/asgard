#Debug

The debug package helps to handle errors and display debugging information to the developer.

- [Installation](#installation)
- [ErrorHandler](#errorhandler)
- [Debug](#debug)

<a name="installation"></a>
##Installation
**If you are working on an Asgard project you don't need to install this library as it is already part of the standard libraries.**

	composer require asgard/debug 0.*

<a name="errorhandler"></a>
##ErrorHandler

**Register an error handler**

	$errorHandler = \Asgard\Debug\ErrorHandler::register();

**Ignore PHP errors in a specific directory**

	$errorHandler->ignoreDir('libs/old_legay_package/');

**Set the logger**

	$errorHandler->setLogger($logger);

The logger should implement [\Psr\Log\LoggerInterface](https://github.com/php-fig/log/blob/master/Psr/Log/LoggerInterface.php).

**Check if the error handler has a logger**

	$errorHandler->isLogging();

**Get the backtrace from an exception**

	$trace = $errorHandler->getBacktraceFromException($e);

**To log PHP errors**

	$errorHandler->setLogPHPErrors(true);

**Log an exception**

	$errorHandler->logException($e);

**Log an error**

	$errorHandler->log($severity, $message, $file, $line, $trace);

Severity should be [one of the these](https://github.com/php-fig/log/blob/master/Psr/Log/LogLevel.php).

**Activate/Desactivate debugging**

	$errorHandler->setDebug(true);
	$errorHandler->setDebug(false);

If debug is set to true, the error handler will display a debugging page to the user when stumbling upon an error, otherwise it will be hidden.

<a name="debug"></a>
##Debug

Display the debug screen:

	\Asgard\Debug\d($var1, $var2, ...);

In an Asgard application, the global function d() is an alias of \Asgard\Debug\d:

	d($var1, $var2, ...);


###Contributing

Please submit all issues and pull requests to the [asgardphp/asgard](http://github.com/asgardphp/asgard) repository.

### License

The Asgard framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)