<?php

$loader = new \Phalcon\Loader();

$loader->registerNamespaces([
    'App' => BASE_PATH . '/app'
]);

$loader->registerFiles([
    BASE_PATH . '/helper.php'
]);

$loader->register();