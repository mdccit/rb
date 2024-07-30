<?php


namespace App\Extra\ThirdPartyAPI;


class GoogleAuthAPI
{
    private $applicationName;
    private $client;
    private $configJson;

    public function __construct()
    {
        $this->applicationName = config('app.name');

        // load our config.json that contains our credentials for accessing google's api as a json string
        $this->configJson = base_path().'/config.json';

        // create the client
        $this->client = new \Google_Client();
        $this->client->setApplicationName($this->applicationName);
        $this->client->setAuthConfig($this->configJson);
        $this->client->setAccessType('offline'); // necessary for getting the refresh token
        $this->client->setApprovalPrompt('force'); // necessary for getting the refresh token
        // scopes determine what google endpoints we can access. keep it simple for now.
        $this->client->setScopes(
            [
                \Google\Service\Oauth2::USERINFO_PROFILE,
                \Google\Service\Oauth2::USERINFO_EMAIL,
                \Google\Service\Oauth2::OPENID,
            ]
        );
        $this->client->setIncludeGrantedScopes(true);
    }

    public function getGoogleAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function getGoogleUser($authCode)
    {
        $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);
        $this->client->setAccessToken(json_encode($accessToken));

        $service = new \Google\Service\Oauth2($this->client);
        return $service->userinfo->get();
    }

    public function getAccessToken()
    {
        return json_encode($this->client->getAccessToken());
    }
}
