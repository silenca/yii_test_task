<?php

namespace app\controllers;

use app\models\ContactContract;
use app\models\ManagerNotification;
use app\models\MissedCall;
use app\models\SipChannel;
use Yii;
use yii\web\Controller;

class BaseController extends Controller
{

    public function beforeAction($action)
    {
        if (Yii::$app->controller->id == 'contacts' && $action->id == 'save') {
            $this->enableCsrfValidation = false;
        }
        if (parent::beforeAction($action)) {
            if (Yii::$app->user->can('notifications')) {
                $notify_count = ManagerNotification::find()->where(['manager_id' => Yii::$app->user->identity->id])->andWhere(['viewed' => 0])->count();
                $this->view->params['notify_count'] = $notify_count;
            }
            if (Yii::$app->user->can('calls')) {
                $missed_count = MissedCall::find()->where(['manager_id' => Yii::$app->user->identity->id])->count();
                $this->view->params['missed_count'] = $missed_count;
                $userData = Yii::$app->user->identity;
                if($userData->int_id && $userData->password_sip) {
                    $this->view->params['sip'] = [
                        'login' => $userData->int_id,
                        'password' => $userData->password_sip,
                    ];
                }
            }
            if (Yii::$app->user->can('contracts')) {
                $new_contract_count = ContactContract::find()->where(['solution_id' => NULL])->count();
                $this->view->params['new_contract_count'] = $new_contract_count;
            }
            if (!Yii::$app->user->getIsGuest()) {
                $user_role = Yii::$app->user->identity->getUserRole();
                $this->view->params['user_role'] = $user_role;
                $user_id = Yii::$app->user->identity->getId();
                $this->view->params['user_id'] = $user_id;
            }
            if (Yii::$app->user->can('use_archived_tags')) {
                $use_archive_tags = Yii::$app->user->identity->getSetting('use_archive_tags');
                $this->view->params['use_archive_tags'] = $use_archive_tags;
            }

            return true;
        }

        return false;
    }

    protected function json($data = [], $status = 200, $errors = [])
    {
//        Yii::$app->response->format = Response::FORMAT_JSON;
//        header('Access-Control-Allow-Origin: *');
//        header('Content-Type: application/json');
        $response = [
            'status' => $status,
        ];
        if ($data !== false) {
            $response['data'] = $data;
        }
        if(is_string($errors) && mb_strlen($errors) > 0){
            $response['errors'][] = $errors;
        }elseif (is_array($errors) && count($errors) > 0) {
            $response['errors'] = $errors;
        }
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        die;
        return;
    }
}
