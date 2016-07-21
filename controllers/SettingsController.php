<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class SettingsController extends BaseController {

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['use-archive-tags'],
                        'allow' => true,
                        'roles' => ['admin'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'use-tags' => ['post']
                ],
            ],
        ];
    }

    public function actionUseArchiveTags() {
        $use = (int)Yii::$app->request->post('use');
        if ($use == 0 || $use == 1) {
            Yii::$app->user->identity->setSetting('use_archive_tags', $use);
            $this->json(false, 200);
        }
        $this->json(false, 415);
    }

}