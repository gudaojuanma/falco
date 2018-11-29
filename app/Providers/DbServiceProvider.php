<?php

namespace App\Providers;

use Phalcon\DiInterface;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di\ServiceProviderInterface;
use App\Core\Db\Dialect\Mysql as Dialect;

class DbServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di)
    {
        $di->set('db', function() {
            $options = config('database.mysql')->toArray();
            $options['dialectClass'] = Dialect::class;
            return new Mysql($options);
        }, true);
    }

}