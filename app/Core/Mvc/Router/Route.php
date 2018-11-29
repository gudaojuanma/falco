<?php

namespace App\Core\Mvc\Router;

use Phalcon\Mvc\Router\Route as BaseRoute;

class Route extends BaseRoute
{
    protected $middleware = [];

    public function setMiddleware($middleware) {
        if (is_string($middleware)) {
            $this->middleware[] = $middleware;
        }

        if (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
        }
    }

    public function getMiddleware() {
        return $this->middleware;
    }
}
