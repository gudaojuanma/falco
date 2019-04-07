<?php

namespace App\Core\Mvc\Router;

use Phalcon\Text;
use Phalcon\Mvc\Router\Group as BaseGroup;

class Group extends BaseGroup
{
    use MiddlewareTrait;

    /**
     * 重写父类添加路由方法，使用增强后的路由类，允许设置中间件
     */
    protected function _addRoute($pattern, $paths = null, $httpMethod = null) {
        $defaultPaths = $this->_paths;

        if (is_array($defaultPaths)) {
            if (is_string($paths)) {
                $processedPaths = Route::getRoutePaths($paths);
            } else {
                $processedPaths = $paths;
            }

            if (is_array($processedPaths)) {
                $mergedPaths = array_merge($defaultPaths, $processedPaths);
            } else {
                $mergedPaths = $defaultPaths;
            }
        } else {
            $mergedPaths = $paths;
        }

        $route = new Route($this->_prefix . $pattern, $mergedPaths, $httpMethod);
        $route->setGroup($this);

        // 将组的中间件设置到路由上面
        $route->setMiddleware($this->middleware);

        $this->_routes[] = $route;
        return $route;
    }
}