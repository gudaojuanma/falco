<?php

namespace App\Library\Guard;

use App\Models\User;
use App\Library\Guard;
use App\Library\GuardInterface;
use Phalcon\Di\Injectable;

class Session extends Guard implements GuardInterface
{
    public function check() {
        if ($this->session->has($this->key())) {
            return true;
        }

        $rememberKey = $this->rememberKey();
        if ($this->cookies->has($rememberKey)) {
            $token = $cookies->get($rememberKey)->getValue();
            $user = User::findFirstByRememberToken($token);
            return $user && $this->login($user);
        }

        return false;
    }

    public function login($user, $remember = false, $days = 15)
    {
        $this->session->set($this->key(), $user->{$this->identifierName});

        if ($remember) {
            $user->updateRememberToken();
            $this->cookies->set($this->rememberKey(), $user->remember_token, time() + $days * 86400, '/', null, null, true);
        }
        
        return true;
    }

    public function user()
    {
        $identifier = $this->session->get($this->key());
        return $this->findUserByIdentifier($identifier);
    }

    public function logout()
    {
        $this->session->destroy();
        $this->cookies->get($this->rememberKey())->delete();
    }
}
