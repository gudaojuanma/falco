<?php

namespace App\Http\Providers;

use App\Models\Asset;
use Phalcon\DiInterface;
use App\Core\Assets\Manager;
use Phalcon\Di\ServiceProviderInterface;

class AssetsServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di)
    {
        $di->set('assets', function() {
            $manager = new Manager();

            $manager->setMap([
                'jquery.js' => '/vendor/jquery/1.12.4/jquery.min.js',
                'bootstrap.js' => '/vendor/bootstrap/3.3.7/js/bootstrap.min.js',
                'swiper.js' => '/vendor/Swiper/4.3.3/js/swiper.min.js',
                'cookie.js' => '/vendor/js.cookie/2.2.0/js.cookie.min.js',
                'bootstrap.css' => '/vendor/bootstrap/3.3.7/css/bootstrap.min.css',
                'swiper.css' => '/vendor/Swiper/4.3.3/css/swiper.min.css'
            ]);

            return $manager;
        });
    }
}