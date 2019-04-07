<?php

namespace App\Http\Middleware;

use Closure;
use App\Core\Mvc\Middleware;

class Authenticate extends Middleware
{
    public function execute(Closure $next) {
        return $next();
    }
}