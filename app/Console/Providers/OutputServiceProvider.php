<?php

namespace App\Console\Providers;

use Phalcon\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use App\Library\Cli\Output;

class OutputServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di)
    {
        $di->set('output', Output::class, true);
    }
}