<?php

namespace App\Library;

interface GuardInterface
{
    public function key();

    public function rememberKey();

    public function user();

    public function guest();

    public function check();

    public function attempt(array $credentials, $remember = false);

    public function login($user, $remember = false, $days = 15);

    public function logout();
}