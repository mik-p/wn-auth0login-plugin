<?php

namespace mikp\auth0login\Classes;

use Auth0\SDK\Auth0;
use Hybridauth\User;

class Auth0Provider
{
    public $auth0;

    public function __construct($id_provider, $client_id, $client_secret, $callback)
    {
        $this->auth0 = new Auth0([
            'domain' => $id_provider,
            'clientId' => $client_id,
            'clientSecret' => $client_secret,
            'scope' => ['openid'],
            'redirectUri' => $callback
        ]);
    }

    public function isConnected()
    {
        return true;
    }

    public function disconnect()
    {
        return true;
    }

    public function authenticate()
    {
        if ($this->auth0->getExchangeParameters()) {
            // If they're present, we should perform the code exchange.
            $this->auth0->exchange();
        }

        // Check if the user is logged in already
        $session = $this->auth0->getCredentials();

        // Is this end-user already signed in?
        if ($session === null) {
            // They are not. Redirect the end user to the login page.
            header('Location: ' . $this->auth0->login());
            exit;
        }
    }

    public function getAccessToken()
    {
        return null;
    }

    public function getUserProfile()
    {
        $session = $this->auth0->getCredentials();

        // Is this end-user already signed in?
        if ($session === null && isset($_GET['code']) && isset($_GET['state'])) {
            if ($this->auth0->exchange() === false) {
                die("Authentication failed.");
            }

            // Authentication complete!
            $auth0_user = $this->auth0->getUser();
        }

        $userProfile = new User\Profile();

        $userProfile->identifier  = $session->user['user_id'];
        $userProfile->username    = $session->user['username'];
        $userProfile->email       = $session->user['email'];
        $userProfile->firstName   = $session->user['given_name'];
        $userProfile->lastName    = $session->user['family_name'];
        $userProfile->displayName = $session->user['name'];
        $userProfile->photoURL    = $session->user['picture'];
        $userProfile->profileURL  = $session->user['profile'];

        return $userProfile;
    }
}
