<?php

namespace libAllure;

use libAllure\AuthBackend;

class AuthBackendOpenId extends AuthBackend
{
    private \LightOpenID $openid;

    public function __construct($domain)
    {
        if ($domain[strlen($domain) - 1] != '/') {
            throw new \Exception('Domain must end with a /');
        }

        $this->openid = new \LightOpenID($domain);
        $this->openid->required = [
            'contact/email'
        ];
    }

    public function checkCredentials($username, $password)
    {
    }

    public function getUserAttributes($username = null)
    {
        return $this->openid->getAttributes();
    }

    public function getEmail()
    {
        $attrs = $this->getUserAttributes();

        return $attrs['contact/email'];
    }

    public function getOpenId()
    {
        return $this->openid;
    }

    public function login($provider)
    {
        switch ($provider) {
            case 'google':
                $this->openid->__set('identity', 'https://www.google.com/accounts/o8/id');
                break;
            default:
                throw new \Exception('Unknown provider');
        }

        header('Location:' . $this->openid->authUrl());
    }

    public function getMode()
    {
        return $this->openid->__get('mode');
    }
}
