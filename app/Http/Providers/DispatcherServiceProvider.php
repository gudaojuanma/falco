<?php

namespace App\Http\Providers;

use Phalcon\DiInterface;
use Phalcon\Events\Manager;
use Phalcon\Di\ServiceProviderInterface;
use App\Core\Mvc\Dispatcher;
use App\Http\EventListeners\DispatcherEventListener;

class DispatcherServiceProvider implements ServiceProviderInterface
{

    public function register(DiInterface $di)
    {
        $di->set('dispatcher', function() {
            $dispatcher = new Dispatcher();
            $eventsManager = new Manager();
            $eventsManager->attach('dispatch', new DispatcherEventListener());
            $dispatcher->setEventsManager($eventsManager);
            $dispatcher->setDefaultNamespace('App\\Http\\Controllers');
            return $dispatcher;
        }, true);
    }

}