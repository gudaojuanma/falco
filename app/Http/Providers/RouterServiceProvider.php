<?php

namespace App\Http\Providers;

use Phalcon\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use App\Core\Mvc\Router;
use App\Http\Routes\Web;

class RouterServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di)
    {
        $provider = $this;

        $di->set('router', function() use($provider) {
            return $provider->generate();
        }, true);
    }

    public function generate()
    {
        $router = new Router(false);
        $router->removeExtraSlashes(true);
        $router->mount(new Web());   
        $router->notFound([
            'namespace' => 'App\\Http\\Controllers',
            'controller' => 'error',
            'action' => 'show404'
        ]);
        return $router;
    }
}
