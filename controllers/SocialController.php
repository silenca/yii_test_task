<?php
namespace app\controllers;

use yii\web\Request;
use yii\web\Response;

class SocialController extends BaseController
{
    const FB_MODE_SUBSCRIBE = 'subscribe';

    public function actionFb()
    {
        $request = \Yii::$app->request;
        try {
            if($request->get('hub_mode')) {
                return $this->getFbSubscribeResponse($request);
            }

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
}