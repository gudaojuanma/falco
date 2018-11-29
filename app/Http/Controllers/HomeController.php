<?php

namespace App\Http\Controllers;

use Phalcon\Mvc\Controller;
use App\Jobs\Greeting;

class HomeController extends Controller
{
    public function indexAction()
    {
        dispatch(Greeting::class, ['Gudaojuanma'], 255, 5);
        $this->assets->pick('jquery.js', 'bootstrap.js', 'bootstrap.css', 'post.js', 'post.css');
        $this->view->pick('home/index');
    }
}