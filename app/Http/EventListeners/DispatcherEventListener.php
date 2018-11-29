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

        try {
            $dispatcherParameters = $dispatcher->getParams();

            $reflection = new ReflectionMethod($controllerClass, $method);
            $parameters = $reflection->getParameters();

            foreach ($parameters as $parameter) {
                $name = $parameter->getName();
                if (isset($dispatcherParameters[$name])) {
                    if (($class = $parameter->getClass())) {
                        $className = $class->name;
                        // 注入模型
                        if (is_subclass_of($className, Model::class)) {
                            $id = $dispatcherParameters[$name];
                            if (($model = $className::findFirstById($id))) {
                                $dispatcherParameters[$name] = $model;
                            } else {
                                $dispatcher->forward([
                                    'controller' => 'error',
                                    'action' => 'show404'
                                ]);
                            }
                        }
                    }
                }
            }
            $dispatcher->setParams($dispatcherParameters);
        } catch (Exception $e) {
            $logger = resolve('logger');
            $logger->error($e->getMessage());
        }
    }

}
