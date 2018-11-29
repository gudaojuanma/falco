<?php 

namespace App\Core\Mvc;

use Phalcon\Http\Request;
use Phalcon\Mvc\Dispatcher as BaseDispatcher;

class Dispatcher extends BaseDispatcher
{
    public function callActionMethod($handler, $actionMethod, $params = [])
    {
        $router = resolve('router');
        $route = $router->getMatchedRoute();
        if(is_null($route)) {
            return call_user_func_array([$handler, $actionMethod], $params);
        }

        $middlewareAlias = config('app.middleware');
        $middlewareNames = $route->getMiddleware();

        $callable = function() use($handler, $actionMethod, $params){
            return call_user_func_array([$handler, $actionMethod], $params);
        };

        while(($middlewareName = array_pop($middlewareNames))) {
            if (isset($middlewareAlias[$middlewareName])) {
                $middlewareClass = $middlewareAlias->get($middlewareName);
                $middlewareInstance = new $middlewareClass();
                $handler = [$middlewareInstance, 'execute'];
                $callable = function() use ($handler, $callable) {
                    return call_user_func($handler, $callable);
                };
            }
        }

        return call_user_func($callable);
    }
}
