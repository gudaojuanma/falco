<?php

namespace App\Console\Tasks;

use Phalcon\Text;
use Phalcon\Cli\Task;
use App\Http\Providers\RouterServiceProvider;

class RouteTask extends Task
{
    /**
     * @description List all registered routes
     */
    public function listAction($params)
    {
        $filter = [];
        foreach (['middleware', 'method', 'module', 'namespace', 'controller'] as $field) {
            $filter[$field] = isset($params[$field]) ? $params[$field] : null;
        }

        $router = (new RouterServiceProvider())->generate();

        $data = [
            [ 
                'Name', 
                'Pattern', 
                'Methods', 
                'Module', 
                'Namesapce', 
                'Controller::Action', 
                'Middlewares'
            ]
        ];

        foreach ($router->getRoutes() as $index => $route)
        {
            $row = [];

            // Name (required)
            $name = $route->getName();
            $row[] = $name;
            
            // Pattern (required)
            $row[] = $route->getPattern();

            // Methods (required)
            $httpMethods = $route->getHttpMethods();
            if (is_array($httpMethods)) {
                if ($filter['method'] && ! in_array($filter['method'], $httpMethods)) continue;
                $row[] =  implode(',', $httpMethods);
            } else {
                if ($filter['method'] && strcmp($filter['method'], $httpMethods) !== 0) continue;
                $row[] =  $httpMethods;
            }

            // Module (optional)
            $paths = $route->getPaths();
            if (isset ($paths['module'])) {
                if ($filter['module'] && strcmp($filter['module'], $paths['module']) !== 0) continue;
                $row[] = $paths['module'];
            } else {
                $row[] = 'default';
            }

            // Namespace (optional)
            if (isset ($paths['namespace'])) {
                if ($filter['namespace'] && strcmp($filter['namespace'], $paths['namespace']) !== 0) continue;
                $row[] = $paths['namespace'];
            } else {
                $row[] = 'App\Http\Controllers';
            }
            
            // Controller::Action (required)
            if ($filter['controller'] && strcmp($filter['controller'], $paths['controller']) !== 0) continue;
            $row[] = Text::camelize($paths['controller'], '_') . '::' . $paths['action'];

            // Middleware
            $row[] = implode(',', $route->getMiddleware());

            $data[] = $row;
        }

        $this->output->table($data);
    }
}
