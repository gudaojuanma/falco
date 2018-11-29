<?php

namespace App\Http\Providers;

use Phalcon\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class SessionServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di)
    {
        // 设置会话存储目录
        session_save_path(realpath(sessions_path()));

        // 设置会话过期时间，默认为20分钟
        // session_set_cookie_params(env('SESSION_LIFETIME', 60 * 20));

        if (!$di->getShared('session')->start()) {
            $this->logger->debug('Session start failed');
        }
    }
}