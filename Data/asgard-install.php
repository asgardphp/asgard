<?php
require_once 'paths.php';
require_once _VENDOR_DIR_.'autoload.php'; #composer autoloader
\Asgard\Core\App::loadDefaultApp();

\Asgard\Orm\MigrationsManager::addMigrationFile(__DIR__.'/migrations/Data.php');
\Asgard\Orm\MigrationsManager::migrate('Data');
