<?php

namespace app\controllers;

use yii\filters\AccessControl;
use Yii;

class IndexController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect('/login');
        }
        $permissions = Yii::$app->authManager->getPermissions();
        foreach ($permissions as $permission) {
            if (Yii::$app->user->can($permission->name)) {
                return $this->redirect("/$permission->name");
            }
        }
        return $this->redirect('/contacts');
    }
}
