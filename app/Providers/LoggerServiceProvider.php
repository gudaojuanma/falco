<?php

namespace App\Providers;

use Phalcon\Logger;
use Phalcon\DiInterface;
use Phalcon\Logger\Adapter\File;
use Phalcon\Di\ServiceProviderInterface;

class LoggerServiceProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di)
    {
        $di->set('logger', function() {
            $keep = config('logging.keep', 7);
            $level = config('logging.level', Logger::ERROR);

            foreach (scandir(logs_path('')) as $fileName) {
                if (preg_match('#^log\-(\d{8})\.log$#', $fileName, $matches)) {
                    $diffTime = time() - strtotime($matches[1]);
                    if ($diffTime > $keep * 86400) {
                        unlink(logs_path($fileName));
                    }
                }
            }

            $today = date('Ymd', time());
            $file = logs_path(sprintf('log-%s.log', $today));
            $logger = new File($file);
            $logger->setLogLevel($level);
            return $logger;
        });
    }
}