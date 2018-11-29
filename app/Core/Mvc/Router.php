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

    /**
     * 重写父类挂载路由组方法，允许在路由组统一设置中间件
     */
    public function mount(GroupInterface $group) {
        $eventsManager = $this->_eventsManager;
        if (is_object($eventsManager)) {
            $eventsManager->fire('router:beforeMount', $this, $group);
        }

        $groupRoutes = $group->getRoutes();
        if (empty($groupRoutes)) {
            throw new Exception("The group of routes does not contain any routes");
        }

        $beforeMatch = $group->getBeforeMatch();
        if (!is_null($beforeMatch)) {
            foreach ($groupRoutes as $route) {
                $route->beforeMatch($beforeMatch);
            }
        }

        $hostname = $group->getHostName();
        if (!is_null($hostname)) {
            foreach ($groupRoutes as $route) {
                $route->setHostName($hostname);
            }
        }

        // 将路由组的中间件设置到组内的每个路由上
        $middleware = $group->getMiddleware();
        if (!is_null($middleware)) {
            foreach ($groupRoutes as $route) {
                $route->setMiddleware($middleware);
            }
        }

        $routes = $this->_routes;
        if (is_array($routes)) {
            $this->_routes = array_merge($routes, $groupRoutes);
        } else {
            $this->_routes = $groupRoutes;
        }

        return $this;
    }
}