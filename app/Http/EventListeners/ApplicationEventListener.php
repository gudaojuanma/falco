<?php

namespace App\Http\EventListeners;

use Phalcon\Di;
use Phalcon\Events\Event;
use Phalcon\Mvc\Application;

class ApplicationEventListener
{
    public function boot(Event $event, Application $application)
    {
        resolve('url')->setBaseUri(config('app.url'));

        $this->setDefaultTimezone();
    }

    public function beforeSendResponse()
    {
        $di = Di::getDefault();
        if ($di->has('beanstalk')) {
            $di->get('beanstalk')->disconnect();
        }
    }

    private function setDefaultTimezone()
    {
        $timezone = trim(config('app.timezone', 'UTC'));
        date_default_timezone_set($timezone);
    }
}