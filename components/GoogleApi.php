<?php

namespace app\components;

use Yii;

class GoogleApi
{
    /* @var \Google_Client */
    public $client;

    /* @var \Google_Service_Calendar */
    private $calendar;

    private $scope;
    public static $scopes = array(
        'calendar' => 'https://www.googleapis.com/auth/calendar',
        'calendar-readonly' => 'https://www.googleapis.com/auth/calendar.readonly'
    );

    private $appName = 'SilentCRM';

    public function __construct($scope)
    {
        $this->client = new \Google_Client();
        $this->scope = $scope;
    }

    public function setCredentialsP12($p12Path, $email)
    {
        $credentials = new \Google_Auth_AssertionCredentials(
            $email,
            $this->scope,
            file_get_contents($p12Path)
        );

        $this->client->setAssertionCredentials($credentials);
    }

    public function setCredentialsJson($jsonPath)
    {
        $this->client->setAuthConfigFile($jsonPath);
        $this->client->setApplicationName($this->appName);
        $this->client->setScopes($this->scope);
    }

    /**
     * @return \Google_Service_Calendar
     */
    public function getCalendar()
    {




        if (!$this->calendar) {
            $this->calendar = new \Google_Service_Calendar($this->client);
        }

        return $this->calendar;
    }

    public function auth()
    {
        $this->setupClient();

        if (isset($_SESSION['google_access_token'])) {
            $this->client->setAccessToken($_SESSION['google_access_token']);
            $this->checkIfAccessExpired($this->client);
        } else {
            $auth_url = $this->client->createAuthUrl();
            Yii::$app->getResponse()->redirect($auth_url)->send();
        }
    }

    public function setupClient()
    {
        $this->setCredentialsJson(Yii::$app->params['google_api_cred_path']);
        $this->client->setAccessType('offline');
//        $google_api->client->setRedirectUri('http://call-center.ru/googleapi/call-back/');
        $this->client->setRedirectUri('http://' . Yii::$app->homeUrl . '/googleapi/oauth-call-back');

        return $this;
    }

    public function checkIfAccessExpired($client)
    {
        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->refreshToken($client->getRefreshToken());
            $_SESSION['google_access_token'] = $client->getAccessToken();
        }
    }

    public function logout() {
        unset($_SESSION['google_access_token']);
    }
}