<?php

namespace App\Providers;

use Dotenv\Dotenv;
use Phalcon\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Config\Adapter\Php as Config;

class ConfigServiceProvider implements  ServiceProviderInterface
{
    protected static $cacheFile = BASE_PATH . '/bootstrap/cache/config.php';

    public static function cache()
    {
        $configData = [];
        $pattern = BASE_PATH . '/config/*.php';
        foreach (glob($pattern) as $filename) {
            $key = substr(basename($filename), 0, -4);
            $configData[$key] = require $filename;
        }

        $content = '<?php return ' . var_export($configData, true) . ';';

        file_put_contents(self::$cacheFile, $content);
    }

    public static function clear()
    {
        unlink(self::$cacheFile);
    }

    public function register(DiInterface $di)
    {
        $di->set('config', function() {
            (new Dotenv(BASE_PATH))->load();

            if (! file_exists(self::$cacheFile)) {
                self::cache();
            }

            return new Config(self::$cacheFile);
        }, true);
    }
}