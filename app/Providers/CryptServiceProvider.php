<?php

namespace App\Providers;

use Phalcon\Crypt;
use Phalcon\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class CryptServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di)
    {
        $di->set('crypt', function() {
            $key = config('app.key');
            $crypt = new Crypt();
            return $crypt->setKey($key);
        });
    }
}