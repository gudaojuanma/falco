<?php

namespace App\Core\Mvc\Router;

use Phalcon\Mvc\Router\Route as BaseRoute;

class Route extends BaseRoute
{
    use MiddlewareTrait;
}
