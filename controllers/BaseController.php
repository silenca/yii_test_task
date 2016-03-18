<?php

namespace app\controllers;

use yii\web\Controller;
use yii\web\Response;
use app\models\User;
use app\models\MissedCall;
use app\models\ManagerNotification;
use app\models\ContactContract;
use Yii;

class BaseController extends Controller {

    public function beforeAction($action) {
        if (parent::beforeAction($action)) {
            if (Yii::$app->user->can('notifications')) {
                $notify_count = ManagerNotification::find()->where(['manager_id' => Yii::$app->user->identity->id])->andWhere(['viewed' => 0])->count();
                $this->view->params['notify_count'] = $notify_count;
            }
            if (Yii::$app->user->can('calls')) {
                $missed_count = MissedCall::find()->where(['manager_id' => Yii::$app->user->identity->id])->count();
                $this->view->params['missed_count'] = $missed_count;
            }
            if (Yii::$app->user->can('contracts')) {
                $new_contract_count = ContactContract::find()->where(['solution_id' => null])->count();
                $this->view->params['new_contract_count'] = $new_contract_count;
            }
            return true;
        }

        return false;
    }

    protected function json($data = [], $status = 200, $errors = []) {
        //Yii::$app->response->format = Response::FORMAT_JSON;
//        header('Access-Control-Allow-Origin: *');
//        header('Content-Type: application/json');
        $response = [
            'status' => $status,
        ];
        if ($data !== false) {
            $response['data'] = $data;
        }
        if (count($errors) > 0) {
            $response['errors'] = $errors;
        }
        echo json_encode($response);
        die;
    }

}
