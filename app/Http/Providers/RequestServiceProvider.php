<?php

namespace App\Http\Providers;

use Phalcon\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use App\Core\Http\Request;

class RequestServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di)
    {
        $di->set('request', function() use($di) {
            $request = new Request();
            $request->setDI($di);
            return $request;
        });
    }
}