<?php
namespace app\controllers;

use Facebook\Facebook;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\Lead;
use FacebookAds\Api as FbAdsApi;
use yii\log\Logger;
use yii\web\Request;

class SocialController extends BaseController
{
    const FB_MODE_SUBSCRIBE = 'subscribe';

    /**
     * @var FbAdsApi
     */
    protected $fbApiInitialized = false;

    public function beforeAction($action)
    {
        if($this->action->id == 'fb') {
            $this->enableCsrfValidation = false;
        }
        return true;
    }

    public function actionFb()
    {
        $request = \Yii::$app->request;
        try {
            if(!$this->initFbApi()) {
                throw new \Exception('Can not initialize FB API');
            }
            if($request->get('hub_mode')) {
                return $this->getFbSubscribeResponse($request);
            } else {
                $fbPayload = json_decode($request->getRawBody(), true);

                $fbObject = $fbPayload['object'];
                foreach ($fbPayload['entry'][0]['changes'] as $fbChange) {
                    try {
                        $action = 'registerFb' . ucfirst($fbObject) . ucfirst($fbChange['field'] ?? '');
                        if (method_exists($this, $action)) {
                            $this->$action($fbChange['value']);
                        } else {
                            $this->log(['item' => $fbChange], 'fb');
                        }
                    } catch (\Exception $e) {
                        $this->log(['error' => [
                            $e->getMessage(),
                            $e->getTraceAsString(),
                        ], 'item' => $fbChange], 'fb');
                    }
                }
            }

            return '';
        } catch(\Exception $e) {
            echo $e->getMessage();die;
        }
    }

    protected function getFbSubscribeResponse(Request $request)
    {
        switch($request->get('hub_mode', '')) {
            case self::FB_MODE_SUBSCRIBE:
                $token = $request->get('hub_verify_token');
                $appToken = \Yii::$app->params['fb']['hub_verify_token'] ?? '';
                if($token === $appToken) {
                    return $request->get('hub_challenge');
                }
                break;
        }

        return '';
    }

    protected function registerFbPageLeadgen(array $data = [])
    {
        $lead = (new Lead($data['leadgen_id']))->getSelf([], [])->exportAllData();
        $this->log(['data' => $data, 'lead' => print_r($lead, true)], 'fb');
        return '';
    }

    protected function initFbApi()
    {
        if(!$this->fbApiInitialized) {
            try {
                $fbParams = \Yii::$app->params['fb'] ?? [];
                FbAdsApi::init(
                    $fbParams['appId'] ?? null,
                    $fbParams['secret'] ?? null,
                    $fbParams['accessToken'] ?? null
                );
                FbAdsApi::instance()->setLogger(new CurlLogger());
                $this->fbApiInitialized = true;
            } catch(\Exception $e) {
                $this->fbApiInitialized = false;
            }
        }

        return $this->fbApiInitialized;
    }

    protected function log($data, $type)
    {
        \Yii::getLogger()->log($data, Logger::LEVEL_INFO, 'app.socials.'.$type);
    }
}