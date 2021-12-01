<?php

namespace mikp\auth0login\SocialLoginProviders;

use Backend\Widgets\Form;
use mikp\auth0login\Classes\Auth0Provider;
use Flynsarmy\SocialLogin\SocialLoginProviders\SocialLoginProviderBase;
use URL;

class Auth0 extends SocialLoginProviderBase
{
    use \Winter\Storm\Support\Traits\Singleton;

    protected $driver = 'Auth0';
    protected $adapter;
    protected $callback;

    /**
     * Initialize the singleton free from constructor parameters.
     */
    protected function init()
    {
        parent::init();
        $this->callback = URL::route('flynsarmy_sociallogin_provider_callback', ['AuthZero'], true);
    }

    public function getAdapter()
    {
        if (!$this->adapter) {
            // Instantiate adapter using the configuration from our settings page
            $providers = $this->settings->get('providers', []);

            $this->adapter = new Auth0Provider(
                @$providers['Auth0']['id_provider'],
                @$providers['Auth0']['client_id'],
                @$providers['Auth0']['client_secret']
            );

            $this->adapter->addScope('openid');
            $this->adapter->addScope('profile');
            $this->adapter->addScope('email');

            $this->adapter->setRedirectURL($this->callback);

            if (config('app.debug')) {
                $this->adapter->setVerifyHost(false);
                $this->adapter->setVerifyPeer(false);
            }
            // $this->adapter->setCertPath('/path/to/my.cert');
        }

        return $this->adapter;
    }

    public function isEnabled()
    {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['Auth0']['enabled']);
    }

    public function isEnabledForBackend()
    {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['Auth0']['enabledForBackend']);
    }

    public function extendSettingsForm(Form $form)
    {
        $form->addFields([
            'noop' => [
                'type' => 'partial',
                'path' => '$/mikp/auth0login/partials/backend/forms/settings/_auth0_info.htm',
                'tab' => 'Auth0',
            ],

            'providers[Auth0][enabled]' => [
                'label' => 'Enabled on frontend?',
                'type' => 'checkbox',
                'comment' => 'Can frontend users log in with Auth0?',
                'default' => 'true',
                'span' => 'left',
                'tab' => 'Auth0',
            ],

            'providers[Auth0][enabledForBackend]' => [
                'label' => 'Enabled on backend?',
                'type' => 'checkbox',
                'comment' => 'Can administrators log into the backend with Auth0?',
                'default' => 'false',
                'span' => 'right',
                'tab' => 'Auth0',
            ],

            'providers[Auth0][id_provider]' => [
                'label' => 'Auth0 Domain',
                'type' => 'text',
                'tab' => 'Auth0',
            ],

            'providers[Auth0][client_id]' => [
                'label' => 'Client ID',
                'type' => 'text',
                'tab' => 'Auth0',
            ],

            'providers[Auth0][client_secret]' => [
                'label' => 'Client Secret',
                'type' => 'text',
                'tab' => 'Auth0',
            ],
        ], 'primary');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToProvider()
    {
        if ($this->getAdapter()->isConnected())
            return \Redirect::to($this->callback);

        $this->getAdapter()->authenticate();
    }

    /**
     * Handles redirecting off to the login provider
     *
     * @return array ['token' => array $token, 'profile' => \Hybridauth\User\Profile]
     */
    public function handleProviderCallback()
    {
        $this->getAdapter()->authenticate();

        $token = [$this->getAdapter()->getAccessToken()];
        $profile = $this->getAdapter()->getUserProfile();

        // Don't cache anything or successive logins to different accounts
        // will keep logging in to the first account
        $this->getAdapter()->disconnect();

        return [
            'token' => $token,
            'profile' => $profile
        ];
    }
}
