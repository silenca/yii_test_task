<?php

namespace app\controllers;

use Yii;
use yii\web\Session;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\forms\LoginForm;

class AuthController extends BaseController
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
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        $model->password = '';
        $errors = $model->getErrors('password');
        return $this->render('login', [
            'model' => $model,
            'errors' => $errors,
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
//        if (Yii::$app->request->isAjax) {
//            $this->json(['result' => 'OK'], 200);
//        }
        return $this->goHome();
    }
}
