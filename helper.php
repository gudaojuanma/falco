<?php

if (!defined('BASE_PATH')) {
    exit(1);
}

if (! function_exists('env')) {
    /**
     * @param string $name
     * @param null $default
     * @return mixed
     */
    function env($name, $default = null)
    {
        return getenv($name) ?: $default;
    }
}

if (! function_exists('register_services')) {
    /**
     * @param string $providerGroup
     */
    function register_services($providerGroup)
    {
        $di = \Phalcon\Di::getDefault();
        $providerClasses = config($providerGroup);
        foreach ($providerClasses as $providerClass) {
            $implements = class_implements($providerClass);
            if (in_array(\Phalcon\Di\ServiceProviderInterface::class, $implements)) {
                $di->register(new $providerClass());
            }
        }
    }
}

if (! function_exists('resolve')) {
    function resolve($service, $shared = true)
    {
        $di = \Phalcon\Di::getDefault();

        return $shared ? $di->getShared($service) : $di->get($service);
    }
}

if (! function_exists('config')) {
    function config($fields, $default = null)
    {
        $config = resolve('config');

        if (strpos($fields, '.') === false) {
            return $config->get($fields, $default);
        }

        $segments = explode('.', $fields);
        foreach ($segments as $segment) {
            if (! isset($config[$segment])) {
                return $default;
            }

            $config = $config->get($segment, $default);
        }

        return $config;
    }
}

if (! function_exists('dispatch')) {
    /**
     * 指派异步任务
     * @param string $classname
     * @param array $parameters
     * @param integer $priority 权重
     * @param integer $delay 延迟执行的时间（秒数）
     * @param integer $ttr 运行时间
     * @return integer
     */
    function dispatch($classname, $parameters = [], $priority = 255, $delay = 0, $ttr = 3600)
    {
        $beanstalk = resolve('beanstalk');

        return $beanstalk->put([
            'classname' => $classname,
            'parameters' => $parameters
        ], [
            'priority' => $priority,
            'delay' => $delay,
            'ttr' => $ttr
        ]);
    }
}

if (! function_exists('config_path')) {
    function config_path($path = '')
    {
        return sprintf('%s/config/%s', BASE_PATH, $path);
    }
}

if (! function_exists('public_path')) {
    function public_path($path = '')
    {
        return str_replace('//', '/', BASE_PATH . '/public/' . $path);
    }
}

if (! function_exists('migrations_path')) {
    function migrations_path($path = '')
    {
        return str_replace('//', '/', BASE_PATH . '/app/Database/Migrations/' . $path);
    }
}

if (! function_exists('resources_path')) {
    function resources_path($path = '')
    {
        return str_replace('//', '/', BASE_PATH . '/resources/' . $path);
    }
}

if (! function_exists('stubs_path')) {
    function stubs_path($path = '')
    {
        return str_replace('//', '/', BASE_PATH . '/resources/stubs/' . $path);
    }
}

if (! function_exists('storage_path')) {
    function storage_path($path = '')
    {
        return str_replace('//', '/', BASE_PATH . '/storage/' . $path);
    }
}

if (! function_exists('logs_path')) {
    function logs_path($path = '')
    {
        return str_replace('//', '/', BASE_PATH . '/storage/logs/' . $path);
    }
}

if (! function_exists('caches_path')) {
    function caches_path($path = '')
    {
        return str_replace('//', '/', BASE_PATH . '/storage/caches/' . $path);
    }
}

if (! function_exists('uploads_path')) {
    function uploads_path($path = '')
    {
        return str_replace('//', '/', BASE_PATH . '/public/uploads/' . $path);
    }
}

if (! function_exists('sessions_path')) {
    function sessions_path($path = '')
    {
        return env('SESSION_SAVE_PATH', str_replace('//', '/', BASE_PATH . '/storage/sessions/' . $path));
    }
}
