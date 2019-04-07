<?php

namespace App\Core\Mvc\Router;

trait MiddlewareTrait
{
    protected $middleware = [];

    /**
     * @param mixed $middleware
     * @return mixed
     */
    public function setMiddleware($middleware) {
        if (empty($middleware)) {
            return false;
        }

        if (is_string($middleware)) {
            $this->middleware[] = $middleware;
        }

        if (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
        }
    }

    /**
     * @return array
     */
    public function getMiddleware() {
        return $this->middleware;
    }
}
