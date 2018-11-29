<?php

namespace App\Core\Http;

use Phalcon\Http\Request as BaseRequest;

class Request extends BaseRequest
{
    public function isMobile()
    {
        $userAgent = $this->getUserAgent();

        return strstr($userAgent, 'Mobile') !== false;
    }
}