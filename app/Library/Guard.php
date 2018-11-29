<?php

namespace App\Library;

use Phalcon\Di\Injectable;
use App\Models\User;

abstract class Guard extends Injectable
{
    protected $messages = [];

    protected $identifierName;

    protected $activeName;

    protected $maxAttempted;

    public function __construct($identifierName = 'email', $activeName = 'is_active', $maxAttempted = 5)
    {
        $this->identifierName = $identifierName;
        $this->activeName = $activeName;
        $this->maxAttempted = $maxAttempted;
    }
    
    public function key()
    {
        return 'falco-guard';
    }

    public function rememberKey()
    {
        return 'falco-remember';
    }

    protected function findUserByIdentifier($identifier)
    {
        $options = [
            'conditions' => sprintf('%s = :identifier:', $this->identifierName),
            'bind' => [
                'identifier' => $identifier
            ]
        ];

        return User::findFirst($options);
    }

    public function attempt(array $credentials, $remember = false)
    {
        if (! isset($credentials[$this->identifierName])) {
            $this->messages[$this->identifierName] = sprintf('credentials field <%s> miss', $this->identifierName);
            return false;
        }

        if (! isset($credentials['password'])) {
            $this->messages['password'] = 'credentials field <password> miss';
            return false;
        }

        $identifier = $credentials[$this->identifierName];
        $password = $credentials['password'];

        if (!($user = $this->findUserByIdentifier($identifier))) {
            $this->messages[$this->identifierName] = '账号不存在';
            return false;
        }

        if ($user->attempted > $this->maxAttempted) {
            $this->messages['form'] = '尝试登陆次数过多';
            return false;
        }

        if (!password_verify($password, $user->password)) {
            $this->messages['password'] = '密码有误';
            $user->update([
                'attempted' => $user->attempted + 1
            ]);
            return false;
        }

        if ($this->activeName && ($user->{$this->activeName} === 0)) {
            $this->messages['form'] = '您的账号已被禁用，请联系管理员';
            return false;
        }
        
        $updateData = [
            'attempted' => 0
        ];

        if (password_needs_rehash($user->password, PASSWORD_DEFAULT)) {
            $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $user->update($updateData);

        return $this->login($user, $remember);
    }

    public function guest()
    {
        return ! $this->check();
    }

    public function getMessages()
    {
        return $this->messages;
    }
}