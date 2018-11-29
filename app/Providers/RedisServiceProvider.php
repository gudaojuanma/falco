<?php

namespace App\Providers;

use Phalcon\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Predis\Client;

class RedisServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di)
    {
        $di->set('redis', function() {
            $config = config('database.redis');
            
            $options = [
                'scheme' => 'tcp',
                'host' => $config->host,
                'port' => $config->port
            ];
            
            if (!empty($config->password)) {
                $options['password'] = $config->password;
            }

            return new Client($options);
        }, true);
    }
}
