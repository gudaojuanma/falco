<?php

namespace App\Http\Providers;


use Phalcon\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Translate\Adapter\NativeArray;

class TranslatorServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di)
    {
        $di->setShared('translator', function() {
            $locale = resolve('locale');

            $path = sprintf('%s/resources/messages/%s.php', BASE_PATH, $locale);
    
            return new NativeArray([
                'content' => require $path
            ]);
        });
    }
}
