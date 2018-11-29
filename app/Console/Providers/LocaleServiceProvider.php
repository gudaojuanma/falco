<?php

namespace App\Console\Providers;

use Phalcon\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class LocaleServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di) 
    {
        $di->set('locale', function() {
            return config('app.language', 'zh');
        }, true);
    }
}