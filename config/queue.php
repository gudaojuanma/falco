<?php

return [
    'beanstalk' => [
        'host' => env('BEANSTALK_HOST', '127.0.0.1'),
        'port' => env('BEANSTALK_PORT', 11300),
        'tube' => env('BEANSTALK_TUBE', 'falco')
    ]
];