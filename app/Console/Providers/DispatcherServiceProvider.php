<?php

namespace App\Console\Providers;

use Phalcon\DiInterface;
use Phalcon\Cli\Dispatcher;
use Phalcon\Di\ServiceProviderInterface;

class DispatcherServiceProvider implements  ServiceProviderInterface
{
    public function register(DiInterface $di)
    {
        $di->set('dispatcher', function() {
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace('App\Console\Tasks');
            $dispatcher->setDefaultTask('list');
            return $dispatcher;
        }, true);
    }
}
