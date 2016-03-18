<?php

namespace app\commands;

use app\components\GoogleApi;
use Yii;
use yii\helpers\Url;
use yii\console\Controller;

class GoogleapiController extends Controller
{

    public function actionShowEvents()
    {
        $scopes = ['https://www.googleapis.com/auth/calendar', 'https://www.googleapis.com/auth/calendar.readonly'];

        $google_api = new GoogleApi($scopes[0]);

        $google_api->setCredentialsJson(Yii::$app->params['google_api_cred_path']);
        $google_api->client->setAccessType('offline');
        $google_api->client->setRedirectUri('http://call-center.ru/googleapi/call-back/');
//        $google_api->client->setRedirectUri('http://' . Url::base() . '/googleapi/call-back');

//        $a = Yii::$app->basePath;
//        $c = Yii::$app->urlManager->getBaseUrl();
//        printf("%s\n", $a);
//        printf("%s\n", $c);

        $cred_path = Yii::getAlias('@app/config/user_cred.json');

        if (filesize($cred_path) != 0) {
            $accessToken = file_get_contents($cred_path);
        } else {
            $authUrl = $google_api->client->createAuthUrl();
            file_put_contents(Yii::getAlias('@app/config/authUrl.txt'), '');
            file_put_contents(Yii::getAlias('@app/config/authUrl.txt'), $authUrl);
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $google_api->client->authenticate($authCode);
            file_put_contents($cred_path, $accessToken);
            printf("Credentials saved to %s\n", $cred_path);
        }


        $google_api->client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($google_api->client->isAccessTokenExpired()) {
            $google_api->client->refreshToken($google_api->client->getRefreshToken());
            file_put_contents($cred_path, $google_api->client->getAccessToken());
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

    public function actionCallBack()
    {

    }
}
