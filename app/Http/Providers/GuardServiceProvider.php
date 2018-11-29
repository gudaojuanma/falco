<?php

namespace App\Http\Providers;

use Phalcon\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use App\Library\Guard\Session;

class GuardServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di)
    {
        $di->set('guard', function() {
            $guard = new Session('username', null);
            $guard->setDI($this);
            return $guard;
        }, true);
    }
}