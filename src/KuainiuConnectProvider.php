<?php

namespace Kuainiu;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

/**
 * Class KuainiuConnectProvider.
 */
class KuainiuConnectProvider extends AbstractProvider implements ProviderInterface
{

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['user_basic']; //'organizations.read'];

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://kuainiu.io/oauth/authorize', $state);
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return 'https://kuainiu.io/oauth/token';
    }

    /**
     * Get the access token with a refresh token.
     *
     * @param string $refresh_token
     *
     * @return array
     */
    public function getRefreshTokenResponse($refresh_token)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            'form_params' => $this->getRefreshTokenFields($refresh_token),
        ]);
        return json_decode($response->getBody(), true);
    }

    /**
     * Get the refresh token fields with a refresh token.
     *
     * @param string $refresh_token
     *
     * @return array
     */
    protected function getRefreshTokenFields($refresh_token)
    {
        
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
        ];
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param string $code
     *
     * @return array
     */
    public function getTokenFields($code)
    {
        return array_add(parent::getTokenFields($code), 'grant_type', 'authorization_code');
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param string $token
     *
     * @return array
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://kuainiu.io/api/user', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param array $user
     *
     * @return \Laravel\Socialite\Two\User
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'chinese_name' => $user['english_name'],
            'english_name' => $user['english_name'],
            'name' => $user['name'],
            'email' => $user['email'],
            'avatar' => $user['avatar'],
        ]);
    }
}