<?php

return [
    // 日志级别
    'level' => env('LOG_LEVEL', \Phalcon\Logger::SPECIAL),

    // 保留天数
    'keep' => env('LOG_KEEP', 7)
];