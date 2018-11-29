<?php

namespace App\Http\Providers;

use Phalcon\Mvc\View;
use Phalcon\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class ViewServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di)
    {
        $di->set('view', function() {
            $view = new View();
            $view->setViewsDir(resources_path('/views'));
            $view->registerEngines([
                '.volt' => 'volt'
            ]);
            $view->setVars([
                'locale' => $this->getShared('locale')
            ]);
            return $view;
        }, true);
    }
}