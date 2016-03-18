<?php

namespace app\controllers;

use app\models\Jivosite;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\ContentNegotiator;
use yii\web\Response;

class JivositeController extends BaseController
{
    public $enableCsrfValidation = false;

    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'create' => ['POST'],
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formatParam' => '_format',
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'application/xml' => Response::FORMAT_XML,
                ],
            ],
        ];
    }

    public function actionCreate() {
        $json = file_get_contents('php://input');
        $post = json_decode($json, true);

        $path = Yii::getAlias('@app') . '/web/mylog.txt';
        $this->_log($post, false, $path);

        $event_name = $post['event_name'];
        if ($event_name == 'offline_message') {
            $jivosite = new Jivosite();
            $post['visitor']['phone'] = preg_replace('/[^0-9]/', '', $post['visitor']['phone']);
            if ($jivosite->add(date('Y-m-d G:i:s', time()), $event_name, $post['visitor']['name'], $post['visitor']['phone'], $post['visitor']['email'], $post['message'])) {
                $jivosite->addManagerNotification($jivosite->id, $jivosite->phone);
            }
        }
        return json_encode(['result' => 'ok']);
    }

    public function _log($var, $clear=FALSE, $path=NULL) {
        if ($var) {
            $date = '====== '.date('Y-m-d H:i:s')." =====\n";
            if (is_array($var) || is_object($var)) {
                $result = print_r($var, 1);
            } else {
                $result = $var." (".gettype($var).")";
            }
            $result .="\n";
            if(!$path)
                $path = dirname(__FILE__) . '/mylog.txt';
            if (!file_exists($path))
                fopen($path, "w");
            if($clear)
                file_put_contents($path, '');
            @error_log($date.$result, 3, $path);
            return true;
        }
        return false;
    }
}