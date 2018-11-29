<?php

namespace App\Providers;

use Dotenv\Dotenv;
use Phalcon\Config;
use Phalcon\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class ConfigServiceProvider implements  ServiceProviderInterface
{
    public function register(DiInterface $di)
    {
        $di->set('config', function() {
            (new Dotenv(BASE_PATH))->load();

            return new Config([
                'app' => require config_path('app.php'),
                'database' => require config_path('database.php'),
                'logging' => require config_path('logging.php'),
                'queue' => require config_path('queue.php'),
            ]);
        }, true);
    }

}