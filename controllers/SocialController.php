<?php
namespace app\controllers;

use yii\log\Logger;
use yii\web\Request;

class SocialController extends BaseController
{
    const FB_MODE_SUBSCRIBE = 'subscribe';

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
        $this->log(['data' => $data], 'fb');
        return '';
    }

    protected function log($data, $type)
    {
        \Yii::getLogger()->log($data, Logger::LEVEL_INFO, 'app.socials.'.$type);
    }
}