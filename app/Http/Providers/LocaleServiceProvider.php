<?php

namespace App\Http\Providers;

use Phalcon\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class LocaleServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di) 
    {
        $cookies = resolve('cookies');
        if ($cookies->has('language')) {
            $cookies->useEncryption(false);
            $cookie = $cookies->get('language');
            $cookies->useEncryption(true);
            $language = $cookie->getValue();
        } else {
            $language = resolve('request')->getBestLanguage();
        }

        if (($index = strpos($language, '-')) !== false) {
            $language = substr($language, 0, $index);
        }

        $languages = config('app.languages')->toArray();
        if (! in_array($language, $languages)) {
            $language = config('app.language', 'zh');
        }

        $di->set('locale', function() use ($language) {
            return $language;
        });
    }
}