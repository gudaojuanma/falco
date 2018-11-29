<?php

define('BASE_PATH', realpath(__DIR__ . '/..'));

error_reporting(E_ALL);

require BASE_PATH . '/vendor/autoload.php';

use Phalcon\Mvc\Application;
use Phalcon\Di\FactoryDefault;
use App\Providers\ConfigServiceProvider;
use Phalcon\Events\Manager as EventsManager;
use App\Http\EventListeners\ApplicationEventListener;

$di = new FactoryDefault();
$di->register(new ConfigServiceProvider());
register_services('app.providers.global');
register_services('app.providers.http');

try {
    $application = new Application($di);
    // $application->useImplicitView(false);
    $eventsManager = new EventsManager();
    $eventsManager->attach('application', new ApplicationEventListener());
    $application->setEventsManager($eventsManager);
    $response = $application->handle();
    $response->send();
} catch (\Exception $ex) {
    $message = get_class($ex) . PHP_EOL . $ex->getMessage() . PHP_EOL . $ex->getTraceAsString();
    $application->logger->error($message);
    $application->response->internalServerError()->send();
}
