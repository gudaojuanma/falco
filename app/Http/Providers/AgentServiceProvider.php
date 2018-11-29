<?php

namespace App\Http\Providers;

use Phalcon\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Jenssegers\Agent\Agent;

class AgentServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di) 
    {
        $di->set('agent', function() {
            return new Agent();
        });
    }
}