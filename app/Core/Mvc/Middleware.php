<?php

namespace App\Core\Mvc;

use Closure;
use Phalcon\Di\Injectable;

abstract class Middleware extends Injectable
{
    public function execute(Closure $next) {
        // @todo
    }
}