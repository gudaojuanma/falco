<?php
/**
 * Created by PhpStorm.
 * User: zan.zhang
 * Date: 2018/6/21
 * Time: 16:46
 */

namespace App\Http\Providers;

use Phalcon\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use App\Core\Http\Response;

class ResponseServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di)
    {
        $di->set('response', function() use($di) {
            $response = new Response();
            $response->setDI($di);
            return $response;
        });
    }
}