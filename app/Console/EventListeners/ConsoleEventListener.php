<?php

namespace App\Console\EventListeners;

use Phalcon\Di;
use Phalcon\Cli\Console;
use Phalcon\Events\Event;

class ConsoleEventListener
{
    public function boot(Event $event, Console $console) 
    {
        $timezone = trim(config('app.timezone', 'UTC'));
        date_default_timezone_set($timezone);

        $console->dispatcher->setDefaultNamespace('App\Console\Tasks');
    }

    public function afterHandleTask(Event $event, Console $console) 
    {
        $di = Di::getDefault();
        if ($di->has('beanstalk')) {
            $di->get('beanstalk')->disconnect();
        }
    }
}