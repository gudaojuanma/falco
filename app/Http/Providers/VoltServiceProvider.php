<?php

namespace App\Http\Providers;

use Phalcon\DiInterface;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Di\ServiceProviderInterface;

class VoltServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di)
    {
        $di->set('volt', function() {
            $view = $this->getShared('view');

            $volt = new Volt($view, $this);

            $volt->setOptions([
                'compiledPath' => storage_path('/views/'),
                'compiledExtension' => '.compiled.php'
            ]);

            // $compiler = $volt->getCompiler();
            // $compiler->addFunction('repeat', 'str_repeat');

            return $volt;
        }, true);
    }

}