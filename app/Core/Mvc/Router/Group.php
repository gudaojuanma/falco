<?php

namespace App\Core\Mvc\Router;

use Phalcon\Text;
use Phalcon\Mvc\Router\Group as BaseGroup;

class Group extends BaseGroup
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

    public function restful($resource, $middleware = []) {
        if (strpos($resource, '.') === false) {
            $pattern = '/' . Text::uncamelize($resource, '-');
            $routeName = $resource;
            $modelName = $resource;
            $controller = ucfirst($routeName);
        } else {
            $pattern = '';
            $routeName = lcfirst(Text::camelize($resource, '.'));
            $segments = explode('.', $resource);
            $modelName = array_pop($segments);
            $controller = ucfirst($routeName);
            foreach ($segments as $segment) {
                $pattern .= sprintf('/%s/{%s:\d+}', Text::uncamelize($segment, '-'), $segment);
            }
            $pattern .= '/' . Text::uncamelize($modelName, '-');
        }

        $this->addGet($pattern, $controller . '::index')
            ->setName($routeName)
            ->setMiddleware($middleware);

        $this->addPost($pattern, $controller . '::store')
            ->setName($routeName . 'Store')
            ->setMiddleware($middleware);

        $this->addGet($pattern . '/{' . $modelName . ':\d+}', $controller . '::show')
            ->setName($routeName . 'Show')
            ->setMiddleware($middleware);

        $this->addPut($pattern . '/{' . $modelName . ':\d+}', $controller . '::update')
            ->setName($routeName . 'Update')
            ->setMiddleware($middleware);

        $this->addDelete($pattern . '/{' . $modelName . ':\d+}', $controller . '::destroy')
            ->setName($routeName . 'Destroy')
            ->setMiddleware($middleware);
    }

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
        $this->_routes[] = $route;
        return $route;
    }
}