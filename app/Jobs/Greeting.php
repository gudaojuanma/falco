<?php

namespace App\Jobs;

use Phalcon\Di\Injectable;
use App\Core\JobInterface;

class Greeting extends Injectable implements JobInterface
{
    public function handle($name = 'Falco') {
        $this->logger->debug('Hi!!!! ' . $name);
    }
}