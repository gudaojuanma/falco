<?php

namespace App\Providers;

use Phalcon\DiInterface;
use Phalcon\Queue\Beanstalk;
use Phalcon\Di\ServiceProviderInterface;

class BeanstalkServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di)
    {
        $di->set('beanstalk', function() {
            $config = config('queue.beanstalk');

            $beanstalk = new Beanstalk([
                'host' => $config->host,
                'port' => $config->port
            ]);

            $beanstalk->choose($config->tube);
            $beanstalk->watch($config->tube);

            return $beanstalk;
        }, true);
    }
}