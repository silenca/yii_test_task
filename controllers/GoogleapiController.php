<?php

namespace app\controllers;

use Yii;
use app\components\GoogleApi;
use yii\helpers\Url;

class GoogleApiController extends BaseController
{

    public function actionAuth()
    {
        $google_api = $this->setupClient();

        if (isset($_SESSION['google_access_token'])) {
            $google_api->client->setAccessToken($_SESSION['google_access_token']);
        } else {
            $auth_url = $google_api->client->createAuthUrl();
            $this->redirect(filter_var($auth_url, FILTER_SANITIZE_URL));
        }




        $calendar_service = $google_api->getCalendar();

        $calendarId = 'primary';
        $optParams = array(
            'maxResults' => 10,
            'orderBy' => 'startTime',
            'singleEvents' => TRUE,
            'timeMin' => date('c'),
        );
        $results = $calendar_service->events->listEvents($calendarId, $optParams);

        if (count($results->getItems()) == 0) {
            print "No upcoming events found.\n";
        } else {
            print "Upcoming events:\n";
            foreach ($results->getItems() as $event) {
                $start = $event->start->dateTime;
                if (empty($start)) {
                    $start = $event->start->date;
                }
                printf("%s (%s)\n", $event->getSummary(), $start);
            }
        }
    }

    public function actionOauthCallBack()
    {
        if (isset($_GET['code'])) {
            $google_api = new GoogleApi(GoogleApi::$scopes['calendar']);
            $google_api = $google_api->setupClient();

            $google_api->client->authenticate($_GET['code']);
            $_SESSION['google_access_token'] = $google_api->client->getAccessToken();
//            $redirect_back = Yii::$app->; // дописать !!!
            $this->redirect(filter_var($redirect_back, FILTER_SANITIZE_URL));
        }
    }

    public function setupClient()
    {
        $scopes = ['https://www.googleapis.com/auth/calendar', 'https://www.googleapis.com/auth/calendar.readonly'];

        $google_api = new GoogleApi($scopes[0]);

        $google_api->setCredentialsJson(Yii::$app->params['google_api_cred_path']);
        $google_api->client->setAccessType('offline');
//        $google_api->client->setRedirectUri('http://call-center.ru/googleapi/call-back/');
        $google_api->client->setRedirectUri('http://' . Yii::$app->homeUrl . '/googleapi/oauth-call-back');

        return $google_api;
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
