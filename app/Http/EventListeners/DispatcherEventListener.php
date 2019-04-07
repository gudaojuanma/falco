<?php

namespace App\Http\EventListeners;

use Exception;
use ReflectionMethod;
use Phalcon\Mvc\Model;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;

class DispatcherEventListener
{
    public function beforeDispatchLoop(Event $event, Dispatcher $dispatcher)
    {
        $controllerClass = $dispatcher->getControllerClass();
        $method = $dispatcher->getActiveMethod();
        $parameters = $dispatcher->getParams();

        try {
            $methodReflection = new ReflectionMethod($controllerClass, $method);
            $parameterReflections = $methodReflection->getParameters();

            foreach ($parameterReflections as $parameterReflection) {
                $name = $parameterReflection->getName();
                if (! isset($parameters[$name])) continue;

                $classReflection = $parameterReflection->getClass();
                if ($classReflection->isSubclassOf(Model::class)) {
                    $modelHandler = [$classReflection->name, 'findFirstById'];
                    if (($model = call_user_func($modelHandler, $parameters[$name]))) {
                        $parameters[$name] = $model;
                        continue;
                    }

                    $dispatcher->forward([
                        'controller' => 'error',
                        'action' => 'show404'
                    ]);
                    break;
                }
            }
            $dispatcher->setParams($parameters);
        } catch (Exception $e) {
            resolve('logger')->error($e->getMessage());
        }
    }

}
