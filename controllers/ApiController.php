<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\components\SessionHelper;

class ApiController extends BaseController {

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'ips' => ['127.0.0.1', '178.20.159.27'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'getonlineusers' => ['get'],
                    'ping' => ['get'],
                ],
            ],
        ];
    }

    public function actionGetonlineusers() {
        $online_user_ids = SessionHelper::getOnlineUserIds();
        $this->json($online_user_ids, 200);
    }
    
    public function actionPing() {
        $this->json(false, 200);
    }

}
