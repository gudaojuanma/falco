<?php

namespace App\Providers;

use Phalcon\DiInterface;
use Phalcon\Cache\Frontend\Data;
use Phalcon\Cache\Backend\File;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Text;


class CacheServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di)
    {
        $di->set('cache', function () {
            $frontend = new Data([
                'lifetime' => 120
            ]);

            return new File($frontend, [
                'cacheDir' => caches_path(),
                'prefix' => Text::uncamelize(config('app.name'), '-'),
                'safeKey' => true
            ]);
        });
    }
}