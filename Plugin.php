<?php

namespace mikp\auth0login;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public $elevated = true;

    public $require = ['Winter.User', 'Flynsarmy.SocialLogin', 'mikp.openidconnect'];

    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }

    public function  register_flynsarmy_sociallogin_providers()
    {
        return [
            'mikp\auth0login\SocialLoginProviders\Auth0' => [
                'label' => 'Auth0',
                'alias' => 'AuthZero',
                'description' => 'Log in with Auth0'
            ],
        ];
    }
}
