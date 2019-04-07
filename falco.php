#!/usr/bin/env php
<?php

define('BASE_PATH', realpath(dirname(__FILE__)));

require BASE_PATH . '/bootstrap/loader.php';
require BASE_PATH . '/vendor/autoload.php';

use Phalcon\Di\FactoryDefault\Cli as FactoryDefault;
use Phalcon\Events\Manager as EventsManager;
use App\Core\Cli\Console;
use App\Providers\ConfigServiceProvider;
use App\Console\EventListeners\ConsoleEventListener;

$di = new FactoryDefault();

register(ConfigServiceProvider::class);
register(config('app.providers.global')->toArray());
register(config('app.providers.console')->toArray());

$eventsManager = new EventsManager();
$eventsManager->attach('console', new ConsoleEventListener());

try {
    $console = new Console($di);
    $console->setEventsManager($eventsManager);
    $console->handle($argv);
} catch (Exception $ex) {
    fprintf(STDERR, "%s:%d\n%s\n\n", $ex->getFile(), $ex->getLine(), $ex->getMessage());
    exit(1);
}