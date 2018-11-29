<?php

namespace App\Http\Controllers;

use Phalcon\Mvc\Controller;

class ErrorController extends Controller
{
    public function show404Action()
    {
        return $this->response->notFound('Not Found');
    }
}