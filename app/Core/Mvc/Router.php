<?php

namespace App\Core\Mvc;

use App\Core\Mvc\Router\Route;
use Phalcon\Mvc\Router as BaseRouter;
use Phalcon\Mvc\Router\GroupInterface;

class Router extends BaseRouter
{
    /**
     * 重写父类添加路由方法，使用增强后的路由类，允许设置中间件
     */
    public function add($pattern, $paths = null, $httpMethods = null, $position = BaseRouter::POSITION_LAST) {
        $route = new Route($pattern, $paths, $httpMethods);

        $this->attach($route, $position);

        return $route;
    }
}
